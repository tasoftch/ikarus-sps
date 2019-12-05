<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\SPS;


use Ikarus\SPS\Exception\PipeException;

/**
 * The pipe allows communication between forked processes, because they don't share the same memory, direct accessing
 * each other is not possible.
 * The pipe MUST be created before forking a process, and then pass it to both. You must define, which process is the sender and which the receiver.
 * @package Ikarus
 */
class Pipe
{
    const BUFFER_SIZE = 2048;

    private $receiver;
    private $sender;

    /**
     * Pipe constructor.
     */
    public function __construct()
    {
        if(socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $fd))
            list($this->receiver, $this->sender) = $fd;
        else {
            $e = new PipeException("Could not create communication sockets");
            $e->setPipe($this);
            throw $e;
        }
    }

    /**
     * Sends data to the other end of pipe, means another process
     * @param $data
     */
    public function sendData($data) {
        $data = serialize($data);
        if(socket_write($this->sender, $data, strlen($data)))
            return;

        $err = socket_last_error($this->sender);
        $e = new PipeException(socket_strerror($err), $err);
        $e->setPipe($this);
        throw $e;
    }

    /**
     * Receives data from another process. This method blocks until data was sent.
     *
     * @param bool $blockThread     Blocks the thread until data is available
     * @return mixed
     */
    public function receiveData(bool $blockThread = true) {
        if($blockThread) {
            $reader = function($socket) {
                if(@feof($socket))
                    return NULL;

                $buf = "";
                while ($out = socket_read($socket, static::BUFFER_SIZE)) {
                    $buf .= $out;
                    if(strlen($out) < static::BUFFER_SIZE) {
                        break;
                    }
                }
                return $buf;
            };
        } else {
            $reader = function ($socket) {
                $buffer = "";
                do {
                    socket_recv($socket, $buf, 1024, MSG_DONTWAIT);
                    if ($buf) {
                        $buffer .= $buf;
                    }
                } while ($buf);
                return $buffer;
            };
        }


        $data = $reader($this->receiver);
        return unserialize($data);
    }

    /**
     * Close the sockets now
     */
    public function __destruct()
    {
        socket_close($this->sender);
        socket_close($this->receiver);
    }
}