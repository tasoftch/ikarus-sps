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


use Ikarus\SPS\Event\DispatchedEventInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use TASoft\EventManager\EventManager;


/**
 * A listen plugin waits until something happens (a specifiv event gets triggered).
 * If so, it will do an action.
 *
 * The objects gets invoked in case of a specific event is triggered
 *
 * The listener plugin must subscribe the event manager in its initialize method.
 *
 * @package Ikarus\Plugin\Listener
 */
interface ListenerPluginInterface extends PluginInterface
{
    /**
     * Returns an array of event names.
     * If an event with such a name gets triggered, this listener plugin gets informed.
     *
     * @return array
     */
    public function getEventNames(): array;

    /**
     * Callback to inform the plugin, that an event was triggered
     *
     * @param string $eventName
     * @param DispatchedEventInterface $event
     * @param EventManager $eventManager
     * @param mixed ...$arguments
     * @return void
     */
    public function __invoke(string $eventName, DispatchedEventInterface $event, EventManager $eventManager, ...$arguments);
}