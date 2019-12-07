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

namespace Ikarus\SPS\Plugin\Listener;


use Ikarus\SPS\Event\TalkbackEvent;
use TASoft\EventManager\EventManager;

class TalkbackSocketClientListener extends AbstractListenerPlugin
{
    private $host;
    private $port;

    /**
     * TalkbackSocketClientListener constructor.
     * @param array $eventNames
     * @param string $host
     * @param int $port
     */
    public function __construct(array $eventNames, string $host = 'localhost', int $port = 55000)
    {
        parent::__construct($eventNames);
        $this->host = $host;
        $this->port = $port;
    }

    public function __invoke(string $eventName, $event, EventManager $eventManager, ...$arguments)
    {
        if($event instanceof TalkbackEvent) {
            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                echo "E: " . socket_strerror(socket_last_error()) . "\n";
                exit();
            }


            $result = @socket_connect($socket, $this->getHost(), $this->getPort());
            if ($result === false) {
                echo "E: " . socket_strerror(socket_last_error($socket)) . "\n";
            }

            if(!@socket_write($socket, $event->getMessage(), strlen($event->getMessage()))) {
                echo "E: Could not send message: " . error_get_last()["message"] , "\n";
            } else
                echo "Message sent.\n";

            socket_close($socket);
        }
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }
}