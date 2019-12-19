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

namespace Ikarus\SPS\Helper;

use Ikarus\SPS\Event\DispatchedEventInterface;
use Ikarus\SPS\Event\DispatchedEventResponseInterface;
use Ikarus\SPS\Event\ResponseEvent;
use Ikarus\SPS\Event\StopEngineEvent;
use Ikarus\SPS\Plugin\PluginManagementInterface;
use TASoft\Util\Pipe;

class PluginManager implements PluginManagementInterface
{
    /** @var Pipe */
    private $eventRunloopPipe;
    /** @var Pipe */
    private $pluginTalkbackPipe;

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        $this->eventRunloopPipe = new Pipe();
        $this->pluginTalkbackPipe = new Pipe();
    }

    /**
     * Blocks the thread until a SPS trigger triggers an event
     * Called from main process
     *
     * @return bool
     */
    public function trapEvent(&$name, &$event, &$arguments) {
        $data = $this->eventRunloopPipe->receiveData();

        if($data) {
            list($name, $event, $arguments) = unserialize( $data );
            return true;
        }
        return false;
    }

    /**
     * Posts a response event to the SPS triggers
     *
     * @param DispatchedEventResponseInterface $response
     */
    public function postResponse(DispatchedEventResponseInterface $response) {
        $data = serialize( $response );
        $this->pluginTalkbackPipe->sendData($data);
    }

    /**
     * @inheritDoc
     */
    public function dispatchEvent(string $eventName, DispatchedEventInterface $event = NULL, ...$arguments)
    {
        $data = serialize([$eventName, $event, $arguments]);
        $this->eventRunloopPipe->sendData($data);
    }

    /**
     * @inheritDoc
     */
    public function requestDispatchedResponse()
    {
        if($data = $this->pluginTalkbackPipe->receiveData()) {
            $response = unserialize($data);
            if($response instanceof DispatchedEventResponseInterface)
                return $response;
        }
        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function stopEngine($code = 0, $reason = ""): bool
    {
        $this->dispatchEvent("", new StopEngineEvent($reason, $code));
        if($resp = $this->requestDispatchedResponse()) {
            if($resp instanceof ResponseEvent) {
                return $resp->isPropagationStopped();
            }
        }
        return false;
    }
}