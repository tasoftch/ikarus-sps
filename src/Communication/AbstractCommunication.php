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

namespace Ikarus\SPS\Communication;


use Ikarus\SPS\Exception\CommunicationException;
use Ikarus\SPS\Exception\SocketException;
use Ikarus\SPS\Exception\SPSException;
use Throwable;

abstract class AbstractCommunication implements CommunicationInterface
{
    private $timeout = 1.0;
    const SOCK_BUFFER_SIZE = 2048;

    /**
     * AbstractCommunication constructor.
     * @param float $timeout
     * @param null $socket
     */
    public function __construct(float $timeout = 1.0, $socket = NULL)
    {
        $this->timeout = $timeout;
        $this->socket = $socket;
    }

    /**
     * This method must be able to establish a connection and return a socket to read and write.
     *
     * Called to establish the connection.
     * @return resource
     */
    abstract protected function establishConnection();

    /**
     * Called after transaction to close the connection.
     *
     * @param $socket
     */
    abstract protected function closeConnection($socket);

    /**
     * Send silently to SPS
     *
     * @param $command
     * @param $error
     * @return string|null
     */
    public function sendSilentlyToSPS($command, &$error = NULL) {
        try {
            return $this->sendToSPS($command);
        } catch (Throwable $exception) {
            $error = $exception;
        }
        return NULL;
    }

    public function sendToSPS($command)
    {
        $socket = $this->establishConnection();
        if(!is_resource($socket)) {
            $e = new SocketException("No connected socket available");
            $e->setSocket($socket);
            throw $e;
        }

        $e = function() {
            $error = error_get_last();
            if($error) {
                $e = new CommunicationException($error["message"], $error["code"]);
                throw $e;
            }
            error_clear_last();
        };

        try {
            error_clear_last();

            fwrite($socket, $command);

            $e();

            $buffer = "";

            while (!feof($socket)) {
                $buffer .= fread($socket, static::SOCK_BUFFER_SIZE);
            }

            $e();

            return $buffer;
        } catch (SPSException $exception) {
            throw $exception;
        } finally {
            $this->closeConnection($socket);
        }
    }


    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }
}