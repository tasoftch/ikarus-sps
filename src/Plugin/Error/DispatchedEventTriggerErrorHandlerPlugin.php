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

namespace Ikarus\SPS\Plugin\Error;


use Ikarus\SPS\Error\Deprecated;
use Ikarus\SPS\Error\ErrorInterface;
use Ikarus\SPS\Error\Exception;
use Ikarus\SPS\Error\Fatal;
use Ikarus\SPS\Error\Notice;
use Ikarus\SPS\Error\Warning;
use Ikarus\SPS\Event\PluginErrorEvent;
use Ikarus\SPS\Event\ResponseEvent;
use Ikarus\SPS\Plugin\Management\TriggeredPluginManagementInterface;

class DispatchedEventTriggerErrorHandlerPlugin extends AbstractDispatchedErrorHandlerPlugin
{
    protected $classNamesMap = [
        Notice::class => 'ERR.NOTICE',
        Warning::class => 'ERR.WARNING',
        Deprecated::class => 'ERR.DEPRECATED',
        Fatal::class => 'ERR.FATAL',
        Exception::class => 'ERR.EXCEPTION'
    ];

    protected $defaultErrorEventName = 'ERR.ERROR';

    public function __construct(array $classNamesMap = [], $defaultErrorEventName = NULL, $error_reporting = E_ALL)
    {
        parent::__construct($error_reporting);
        if($classNamesMap)
            $this->classNamesMap = array_merge($this->classNamesMap, $classNamesMap);
        if($defaultErrorEventName)
            $this->defaultErrorEventName = $defaultErrorEventName;
    }

    /**
     * @return array
     */
    public function getClassNamesMap(): array
    {
        return $this->classNamesMap;
    }

    /**
     * @return string
     */
    public function getDefaultErrorEventName(): string
    {
        return $this->defaultErrorEventName;
    }

    protected function handleError(ErrorInterface $error, TriggeredPluginManagementInterface $management): bool
    {
        $eventName = $this->getClassNamesMap()[ get_class($error) ] ?? $this->getDefaultErrorEventName();

        $management->dispatchEvent($eventName, new PluginErrorEvent($error->getCode(), $error->getMessage(), $error->getFile(), $error->getLine(), get_class($error)));
        $event = $management->requestDispatchedResponse();
        if($event instanceof ResponseEvent) {
            if($event->isPropagationStopped()) {
                // Do not force to stop the engine, just shut down the plugin
                if($error instanceof Fatal)
                    exit();

                return true;
            }
        }
        // If the error was not handled by the event listeners, follow the default.
        return parent::handleError($error, $management);
    }
}