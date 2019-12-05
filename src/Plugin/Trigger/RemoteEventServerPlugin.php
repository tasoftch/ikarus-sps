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

namespace Ikarus\SPS\Plugin\Trigger;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\PluginManagementInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;
use Ikarus\SPS\Event\ResponseEvent;

class RemoteEventServerPlugin extends AbstractPlugin implements TriggerPluginInterface, TearDownPluginInterface
{
    private $address;
    private $port;

    /** @var string */
    private $startMessage;

    private $sock, $msgsock;

    /**
     * Listen on address and port for incoming commands
     *
     * @param string $address
     * @param int $port
     * @param string $startMessage
     */
    public function __construct($address, $port, $startMessage = 'Welcome to Remote Event Server of Ikarus SPS!')
    {
        parent::__construct("");
        $this->address = $address;
        $this->port = $port;
        $this->startMessage = $startMessage;
    }

    public function run(PluginManagementInterface $manager)
    {
        if (($this->sock = $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            trigger_error( "socket_create() failed: " . socket_strerror(socket_last_error()), E_USER_WARNING);
        }

        if (socket_bind($sock, $this->address, $this->port) === false) {
            trigger_error( "socket_bind() failed: " . socket_strerror(socket_last_error($sock)), E_USER_WARNING);
        }

        if (socket_listen($sock, 5) === false) {
            trigger_error( "socket_listen() failed: " . socket_strerror(socket_last_error($sock)), E_USER_WARNING);
        }

        do {
            if (($this->msgsock = $msgsock = socket_accept($sock)) === false) {
                trigger_error( "socket_accept() failed: " . socket_strerror(socket_last_error($sock)), E_USER_WARNING);
                break;
            }
            /* Anweisungen senden. */
            if($msg = $this->startMessage)
                socket_write($msgsock, $msg, strlen($msg));

            if(socket_getpeername($msgsock, $addr, $prt)) {
                echo "Connected to $addr on $prt\n";
            }

            do {
                if (false === ($buf = socket_read ($msgsock, 2048, 0))) {
                    trigger_error( "socket_read() failed: " . socket_strerror(socket_last_error($msgsock)) , E_USER_WARNING);
                    break;
                }

                if (!$buf = trim ($buf)) {
                    continue;
                }


                $buf = preg_split("/\s+/i", $buf);
                $cmd = array_shift($buf);

                $manager->dispatchEvent( strtoupper($cmd), new ResponseEvent("Command $cmd not found"), ...$buf );
                $response = $manager->requestDispatchedResponse()->getResponse();

                socket_write ($msgsock, $response, strlen ($response));
            } while (true);
            socket_close ($msgsock);
            $this->msgsock = NULL;
        } while(1);
        socket_close ($sock);
        $this->sock = NULL;
    }

    public function tearDown()
    {
        if($this->msgsock)
            socket_close($this->msgsock);
        if($this->sock)
            socket_close($this->sock);
    }
}