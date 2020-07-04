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


use Ikarus\SPS\Alert\AlertInterface;
use Ikarus\SPS\Exception\InterruptException;
use Ikarus\SPS\Exception\SPSException;
use Ikarus\SPS\Helper\CyclicPluginManager;
use Ikarus\SPS\Plugin\Cyclic\CyclicPluginInterface;
use Ikarus\SPS\Plugin\Cyclic\UpdateOncePluginInterface;
use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use TASoft\Collection\PriorityCollection;
use TASoft\Util\ValueInjector;

class CyclicEngine extends AbstractEngine implements CyclicEngineInterface
{
    /** @var int */
    private $frequency;
    /** @var PriorityCollection */
    private $cyclicPlugins;

    private $pluginManager;

    public static $pluginManagementClassName = CyclicPluginManager::class;


    public function __construct(int $frequency = 2, $name = 'Ikarus SPS, (c) by TASoft Applications')
    {
        parent::__construct($name);
        $this->frequency = $frequency;
        $this->cyclicPlugins = new PriorityCollection();
    }

    protected function shouldAddPlugin(PluginInterface $plugin, int $priority): bool
    {
        if($plugin instanceof CyclicPluginInterface)
            $this->cyclicPlugins->add($priority, $plugin);
        return parent::shouldAddPlugin($plugin, $priority);
    }

    protected function willRemovePlugin(PluginInterface $plugin): bool
    {
        $this->cyclicPlugins->remove($plugin);
        return parent::willRemovePlugin($plugin);
    }

    public function stop($code = 0, $reason = "")
    {
        $this->exitCode = $code;
        $this->exitReason = $reason;

        parent::stop();
        $this->running = false;
    }

    public function getPluginManager(): CyclicPluginManagementInterface
    {
        if(!$this->pluginManager)
            $this->pluginManager = $this->makeCycliclPluginManager();
        return $this->pluginManager;
    }

    /**
     * @param CyclicPluginManagementInterface $pluginManager
     */
    public function setPluginManager(CyclicPluginManagementInterface $pluginManager): void
    {
        $this->pluginManager = $pluginManager;
    }

    protected function makeCycliclPluginManager() {
        $c = static::$pluginManagementClassName;
        return new $c();
    }

    function runEngine()
    {
        $scheduler = [];

        $_TS = microtime(true);

        /** @var CyclicPluginInterface $plugin */
        foreach($this->cyclicPlugins->getOrderedElements() as $plugin) {
            $scheduler[ $plugin->getIdentifier() ] = $_TS;
        }

        if(!$scheduler) {
            throw new SPSException("Engine does not have any plugin", 13);
        }

        $manager = $this->getPluginManager();
        $vi = new ValueInjector($manager, CyclicPluginManager::class);
        $vi->f = function() { return $this->getFrequency(); };

        $schedule = function($freq) use (&$scheduler, &$plugin) {
            $freq = 1 / $freq;
            $scheduler[ $plugin->getIdentifier() ] = microtime(true) + $freq;
        };

        $vi->rtf = function($of) use ($schedule) {
            if($of <= 0)
                $of = $this->getFrequency();
            $schedule($of);
            return true;
        };
        $vi->se = function($c, $r) {
            $this->stop($c, $r);
            return true;
        };
        $vi->tra = function(AlertInterface $alert) {
            foreach($this->alertHandlerPlugins as $plugin) {
                if($plugin->handleAlert($alert))
                    break;
            }
        };
        $vi->qra = function($alert) {
            foreach($this->alertHandlerPlugins as $plugin) {
                if($plugin->recoverAlert($alert))
                    break;
            }
        };

        if(function_exists("pcntl_signal")) {
            $handler = function() use ($manager) {
                $this->tearDownEngine();
                if(method_exists($manager, 'tearDown'))
                	$manager->tearDown();
                exit();
            };

            pcntl_signal(SIGTERM, $handler);
            pcntl_signal(SIGINT, $handler);
        }

		if(method_exists($manager, 'setup'))
			$manager->setup();

		$once = true;

        while ($this->isRunning()) {
            $waitFor = min(array_values($scheduler)) - microtime(true);
            if($waitFor > 0) {
                declare(ticks=1) {
                    usleep($waitFor * 1e6);
                }
            }

            if(!$this->isRunning())
                break;

			$manager->beginCycle();
			foreach($this->cyclicPlugins->getOrderedElements() as $plugin) {
				if($once && $plugin instanceof UpdateOncePluginInterface)
					$plugin->updateOnce($manager);

                if($scheduler[$plugin->getIdentifier()] < microtime(true)) {
                    $schedule( $this->getFrequency() );
                    try {
                        $plugin->update($manager);
                    } catch (InterruptException $exception) {
                        if(!$this->handleInterruption($exception, $manager)) {
                        	$manager->leaveCycle();
							throw $exception;
						}
                    }
                    if(!$this->isRunning())
                        break;
                }
            }
			$once = false;
			$manager->leaveCycle();
        }
		if(method_exists($manager, 'tearDown'))
			$manager->tearDown();
    }


    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }
}