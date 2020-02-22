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

use Ikarus\SPS\Exception\InterruptException;
use Ikarus\SPS\Plugin\Alert\AlertPluginInterface;
use Ikarus\SPS\Plugin\EngineDependentPluginInterface;
use Ikarus\SPS\Plugin\InterruptPluginInterface;
use Ikarus\SPS\Plugin\Management\PluginManagementInterface;
use Ikarus\SPS\Plugin\PluginChildrenInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;
use TASoft\Collection\PriorityCollection;

abstract class AbstractEngine implements EngineInterface
{
    /** @var string  */
    private $name;
    /** @var bool  */
    protected $running = false;
    /** @var PriorityCollection */
    protected $plugins;
    /** @var AlertPluginInterface[] */
    protected $alertHandlerPlugins;
    /** @var PriorityCollection */
    protected $interruptionPlugins;
    /** @var callable|null */
    protected $cleanupHandler;

    protected $exitCode = 0;
    protected $exitReason = "";

    const RUNLOOP_CONTINUE = 1;
    const RUNLOOP_STOP_ENGINE = 2;

    public function __construct($name = 'Ikarus SPS, (c) by TASoft Applications')
    {
        $this->plugins = new PriorityCollection();
        $this->interruptionPlugins = new PriorityCollection();

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Registers a plugin to use while engine runtime
     *
     * @param PluginInterface $plugin
     * @param int $priority
     * @return bool
     */
    public function addPlugin(PluginInterface $plugin, int $priority = 0) {
        if($this->isRunning())
            return false;

        if(!$this->plugins->contains($plugin) && $this->shouldAddPlugin($plugin, $priority)) {
            $this->plugins->add($priority, $plugin);
            if($plugin instanceof PluginChildrenInterface) {
                foreach($plugin->getChildPlugins() as $p)
                    $this->addPlugin($p, $priority);
            }
            if($plugin instanceof InterruptPluginInterface) {
                $this->interruptionPlugins->add($priority, $plugin);
            }
            return true;
        }

        return false;
    }

    /**
     * This method get asked to add a plugin
     *
     * @param PluginInterface $plugin
     * @param int $priority
     * @return bool
     */
    protected function shouldAddPlugin(PluginInterface $plugin, int $priority): bool {
        return true;
    }

    /**
     * This method get asked before removing a plugin
     *
     * @param PluginInterface $plugin
     * @return bool
     */
    protected function willRemovePlugin(PluginInterface $plugin): bool {
        return true;
    }

    /**
     * @param $plugin
     * @return bool
     */
    public function removePlugin($plugin) {
        if($this->isRunning())
            return false;
        if($this->willRemovePlugin($plugin))
            $this->plugins->remove($plugin);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @inheritDoc
     */
    public function getPlugins(): array {
        return $this->plugins->getOrderedElements();
    }

    /**
     * @inheritDoc
     */
    public function stop()
    {
        if($this->isRunning()) {
            $this->tearDownEngine();
            $this->running = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        if(!$this->isRunning()) {
            $this->running = true;
            $this->setupEngine();
            try {
                $this->exitCode = $this->runEngine();
            } catch (\Throwable $throwable) {
                $this->tearDownEngine();
                throw $throwable;
            }

        }
        return $this->exitCode;
    }

    /**
     * Runs the engine
     */
    abstract function runEngine();

    /**
     * Internal call to setup engine
     */
    protected function setupEngine() {
        $this->alertHandlerPlugins = [];

        foreach($this->getPlugins() as $plugin) {
            if($plugin instanceof SetupPluginInterface)
                $plugin->setup();
            if($plugin instanceof EngineDependentPluginInterface)
                $plugin->setEngine( $this );
            if($plugin instanceof AlertPluginInterface)
                $this->alertHandlerPlugins[] = $plugin;
        }
    }

    /**
     * @param int $code
     * @param string $reason
     * @return int
     */
    protected function shouldTerminate($code=0,$reason=""): int {
        return static::RUNLOOP_STOP_ENGINE;
    }

    /**
     * Internal call to tear down engine
     */
    protected function tearDownEngine() {
        foreach($this->getPlugins() as $plugin) {
            if($plugin instanceof TearDownPluginInterface)
                $plugin->tearDown();
            if($plugin instanceof EngineDependentPluginInterface)
                $plugin->setEngine( NULL );
        }

        if(is_callable($cb = $this->getCleanUpHandler()))
            call_user_func($cb);
    }

    /**
     * Internal call to handle interruptions
     *
     * @param InterruptException $exception
     * @param PluginManagementInterface $management
     * @return bool
     */
    protected function handleInterruption(InterruptException $exception, PluginManagementInterface $management): bool {
        /** @var InterruptPluginInterface $plugin */
        foreach($this->interruptionPlugins->getOrderedElements() as $plugin) {
            if($plugin->performInterrupt($exception, $management))
                return true;
        }
        return false;
    }

    /**
     * @return callable|null
     */
    public function getCleanupHandler(): ?callable
    {
        return $this->cleanupHandler;
    }

    /**
     * @param callable|null $cleanupHandler
     */
    public function setCleanupHandler(?callable $cleanupHandler): void
    {
        $this->cleanupHandler = $cleanupHandler;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @return string
     */
    public function getExitReason(): string
    {
        return $this->exitReason;
    }
}