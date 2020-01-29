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


use Ikarus\SPS\Event\DispatchedEvent;
use Ikarus\SPS\Event\DispatchedEventResponseInterface;
use Ikarus\SPS\Event\StopEngineEvent;
use Ikarus\SPS\Helper\ProcessManager;
use Ikarus\SPS\Helper\TriggeredPluginManager;
use Ikarus\SPS\Plugin\DispatchedErrorHandlerPluginInterface;
use Ikarus\SPS\Plugin\Listener\ListenerPluginInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\Trigger\TriggerPluginInterface;
use TASoft\Collection\PriorityCollection;
use TASoft\EventManager\EventManager;
use Throwable;

class TriggeredEngine extends AbstractEngine implements TriggeredEngineInterface
{
    /** @var EventManager */
    protected $eventManager;
    /** @var TriggeredPluginManager */
    protected $pluginManager;
    /** @var ProcessManager */
    protected $processManager;
    /** @var PriorityCollection */
    protected $errorPlugins;

    const RUNLOOP_SKIP_EVENT = 3;

    public function __construct($name = 'Ikarus SPS, (c) by TASoft Applications')
    {
        parent::__construct($name);
        $this->eventManager = new EventManager();
        $this->pluginManager = new TriggeredPluginManager();
        $this->processManager = new ProcessManager();
        $this->errorPlugins = new PriorityCollection();
    }

    protected function shouldAddPlugin(PluginInterface $plugin, int $priority): bool
    {
        if($plugin instanceof DispatchedErrorHandlerPluginInterface)
            $this->errorPlugins->add($priority, $plugin);
        return parent::shouldAddPlugin($plugin, $priority);
    }

    protected function willRemovePlugin(PluginInterface $plugin): bool
    {
        $this->errorPlugins->remove($plugin);
        return parent::willRemovePlugin($plugin);
    }

    function runEngine()
    {
        if($this->processManager->isMainProcess()) {
            $this->running = true;
            $this->setupEngine();

            try {
                $this->exitReason = "";
                $this->eventManager->trigger("INIT", new DispatchedEvent());

                while ( $this->pluginManager->trapEvent($name, $event, $arguments) ) {
                    if($event instanceof StopEngineEvent) {
                        $this->exitReason = $event->getResponse();
                        $event->stopPropagation();
                        $this->pluginManager->postResponse($event);

                        return $event->getCode();
                    }
                    if(!$event)
                        $event = new DispatchedEvent();

                    $code = $this->willHandleDispatchedEvent($name, $event, $arguments);
                    if($code == static::RUNLOOP_STOP_ENGINE)
                        break;
                    if($code == static::RUNLOOP_SKIP_EVENT)
                        continue;

                    $event = $this->eventManager->trigger($name, $event, ...$arguments);

                    $code = $this->didHandleDispatchedEvent($name, $event, $arguments);
                    if($code == static::RUNLOOP_STOP_ENGINE)
                        break;
                    if($code == static::RUNLOOP_SKIP_EVENT)
                        continue;

                    if($event instanceof DispatchedEventResponseInterface) {
                        $this->pluginManager->postResponse($event);
                    }
                }
            }
            catch (Throwable $throwable) {
                throw $throwable;
            } finally {
                $this->stop();
            }
        } else
            trigger_error("Can only run from main process", E_USER_WARNING);
        return 0;
    }

    protected function setupEngine()
    {
        parent::setupEngine();

        $setupErrorEnv = function() {
            foreach($this->errorPlugins->getOrderedElements() as $plugin) {
                if($plugin instanceof DispatchedErrorHandlerPluginInterface) {
                    $plugin->setupErrorEnvironment( $this->pluginManager );
                }
            }
        };

        foreach ($this->plugins->getOrderedElements() as $plugin) {
            if($plugin instanceof TriggerPluginInterface) {
                $this->processManager->fork($plugin);
                if(!$this->processManager->isMainProcess()) {
                    // Child process
                    usleep(1000);
                    $setupErrorEnv();
                    $plugin->run( $this->pluginManager );
                    exit();
                }
            } elseif($plugin instanceof ListenerPluginInterface) {
                if($names = $plugin->getEventNames()) {
                    foreach($names as $name) {
                        $this->eventManager->addListener($name, $plugin);
                    }
                }
            }
        }
    }

    protected function tearDownEngine()
    {
        $this->processManager->killAll();
        $this->processManager->waitForAll();
        $this->eventManager->removeAllListeners();

        parent::tearDownEngine();
    }

    /**
     * Called before triggering an event coming from an SPS trigger
     *
     * @param $name
     * @param $event
     * @param $arguments
     * @return int
     */
    protected function willHandleDispatchedEvent($name, $event, $arguments): int {
        return static::RUNLOOP_CONTINUE;
    }

    /**
     * Called after triggering an event coming from an SPS trigger, but before sending response events back to SPS.
     *
     * @param $name
     * @param $event
     * @param $arguments
     * @return int
     */
    protected function didHandleDispatchedEvent($name, $event, $arguments): int {
        if(strcasecmp($name, 'quit') === 0) {
            return static::RUNLOOP_STOP_ENGINE;
        }
        return static::RUNLOOP_CONTINUE;
    }
}