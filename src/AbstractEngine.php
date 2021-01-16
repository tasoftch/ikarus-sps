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

use Ikarus\SPS\AlertManager\AlertManagerInterface;
use Ikarus\SPS\AlertManager\DefaultAlertManager;
use Ikarus\SPS\Exception\ImmutableSPSException;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Plugin\TearDownPluginInterface;
use Ikarus\SPS\Register\InternalMemoryRegister;
use Ikarus\SPS\Register\MemoryRegisterInterface;
use TASoft\Collection\PriorityCollection;

abstract class AbstractEngine implements EngineInterface
{
    /** @var string  */
    private $name;
    /** @var bool  */
    protected $running = false;
    /** @var PriorityCollection */
    protected $plugins;
    /** @var callable|null */
    protected $cleanupHandler;

    protected $exitCode = 0;
    protected $exitReason = "";

	/** @var MemoryRegisterInterface */
	private $memoryRegister;
	/** @var AlertManagerInterface */
	private $alertManager;

    /** @var AbstractEngine|null */
    private static $runningEngine;

    public function __construct($name = 'Ikarus SPS, (c) by TASoft Applications')
    {
        $this->plugins = new PriorityCollection();
        $this->name = $name;
    }

    /**
     * @return AbstractEngine|null
     */
    public static function getRunningEngine(): ?AbstractEngine
    {
        return self::$runningEngine;
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
     * @return static
     */
    public function addPlugin(PluginInterface $plugin, int $priority = 0) {
        if($this->isRunning())
            throw new ImmutableSPSException("Can not add plugins while Ikarus SPS is running", 182);

        if(!$this->plugins->contains($plugin) && $this->shouldAddPlugin($plugin, $priority)) {
            $this->plugins->add($priority, $plugin);
        }
        return $this;
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
     * @return static
     */
    public function removePlugin($plugin) {
		if($this->isRunning())
			throw new ImmutableSPSException("Can not remove plugins while Ikarus SPS is running", 183);

        if($this->willRemovePlugin($plugin))
            $this->plugins->remove($plugin);
        return $this;
    }

	/**
	 * @return MemoryRegisterInterface
	 */
	public function getMemoryRegister(): MemoryRegisterInterface
	{
		if(!$this->memoryRegister)
			$this->setMemoryRegister( new InternalMemoryRegister() );
		return $this->memoryRegister;
	}

	/**
	 * @param MemoryRegisterInterface $memoryRegister
	 * @return static
	 */
	public function setMemoryRegister(MemoryRegisterInterface $memoryRegister)
	{
		$this->memoryRegister = $memoryRegister;
		if($memoryRegister instanceof EngineDependencyInterface)
			$memoryRegister->setEngine($this);
		return $this;
	}

	/**
	 * @return AlertManagerInterface
	 */
	public function getAlertManager(): AlertManagerInterface
	{
		if(!$this->alertManager)
			$this->setAlertManager( new DefaultAlertManager() );
		return $this->alertManager;
	}

	/**
	 * @param AlertManagerInterface $alertManager
	 * @return static
	 */
	public function setAlertManager(AlertManagerInterface $alertManager)
	{
		$this->alertManager = $alertManager;
		if($alertManager instanceof EngineDependencyInterface)
			$alertManager->setEngine($this);
		return $this;
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
    public function stop($code = 0, $reason = "")
    {
        if($this->isRunning()) {
			$this->exitCode = $code;
			$this->exitReason = $reason;

            $this->tearDownEngine();

            $this->running = false;
            self::$runningEngine = NULL;
        }
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        if(!$this->isRunning()) {
            $this->running = true;
            self::$runningEngine = $this;

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
        foreach($this->getPlugins() as $plugin) {
            if($plugin instanceof EngineDependencyInterface)
                $plugin->setEngine( $this );
            if($plugin instanceof SetupPluginInterface)
                $plugin->setup();
        }
    }

    /**
     * Internal call to tear down engine
     */
    protected function tearDownEngine() {
        foreach($this->getPlugins() as $plugin) {
            if($plugin instanceof TearDownPluginInterface)
                $plugin->tearDown();
            if($plugin instanceof EngineDependencyInterface)
                $plugin->setEngine( NULL );
        }

        if(is_callable($cb = $this->getCleanUpHandler()))
            call_user_func($cb);
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
	 * @return static
     */
    public function setCleanupHandler(?callable $cleanupHandler)
    {
        $this->cleanupHandler = $cleanupHandler;
        return $this;
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