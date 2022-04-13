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

use Ikarus\SPS\AlertManager\UpdatedAlertManagerInterface;
use Ikarus\SPS\Exception\EngineControlException;
use Ikarus\SPS\Exception\SPSException;
use Ikarus\SPS\Plugin\CycleAwarePluginInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Register\WorkflowDependentMemoryRegister;

class CyclicEngine extends AbstractEngine
{
    /** @var int */
    private $interval;

	/**
	 * CyclicEngine constructor.
	 * Pass an interval in milli seconds (ms) ex: 2000 to update each 2 seconds.
	 * @param int $interval
	 * @param string $name
	 */
    public function __construct(int $interval = 2000, $name = 'Ikarus SPS, (c) by TASoft Applications')
    {
        parent::__construct($name);
        $this->interval = $interval;
    }

    /**
     * @inheritDoc
     */
    function runEngine()
    {
        $scheduler = [];
        $cycleAwarePlugins = [];

        $_TS = microtime(true);
        $plugins = $this->getPlugins();

        /** @var PluginInterface $plugin */
        foreach($plugins as $plugin) {
            $scheduler[ $plugin->getIdentifier() ] = $_TS;
            if($plugin instanceof CycleAwarePluginInterface)
            	$cycleAwarePlugins[] = $plugin;
        }

        if(!$scheduler) {
            throw new SPSException("Engine does not have any plugin", 13);
        }

        $schedule = function($freq) use (&$scheduler, &$plugin) {
        	$freq /= 1000;
            $scheduler[ $plugin->getIdentifier() ] = microtime(true) + $freq;
        };

        $memReg = $this->getMemoryRegister();

        if(function_exists("pcntl_signal")) {
            $handler = function() use ($memReg) {
                $this->tearDownEngine();
                if($memReg instanceof WorkflowDependentMemoryRegister)
					$memReg->tearDown();
                exit();
            };

            pcntl_signal(SIGTERM, $handler);
            pcntl_signal(SIGINT, $handler);
        }

		if($memReg instanceof WorkflowDependentMemoryRegister)
			$memReg->setup();

		array_walk($plugins, function(PluginInterface $p) use ($memReg) {
			$p->initialize($memReg);
		});

		$am = $this->getAlertManager();
		if($am instanceof EngineDependencyInterface)
			$am->setEngine($this);

		$stopCycle = function(EngineControlException $exception) {
			if($exception->getControl() == $exception::CONTROL_STOP_CYCLE)
				return true;
			return false;
		};

		$stopEngine = function(EngineControlException $exception) {
			if($exception->getControl() == $exception::CONTROL_STOP_ENGINE) {
				$this->stop( $exception->getCode(), $exception->getMessage() );
				return true;
			}
			return false;
		};

		$crashEngine = function(EngineControlException $exception) use ($memReg) {
			if($exception->getControl() == $exception::CONTROL_CRASH_ENGINE) {
				if ($memReg instanceof WorkflowDependentMemoryRegister) {
					$memReg->endCycle();
					$memReg->tearDown();
				}
				return true;
			}
			return false;
		};


        while ($this->isRunning()) {
            $waitFor = min(array_values($scheduler)) - microtime(true);
            if($waitFor > 0) {
                declare(ticks=1) {
                    usleep($waitFor * 1e6);
                }
            }

            if(!$this->isRunning())
                break;

            try {
				if($memReg instanceof WorkflowDependentMemoryRegister)
					$memReg->beginCycle();

				array_walk($cycleAwarePlugins, function(CycleAwarePluginInterface $plugin) use ($memReg) {
					$plugin->beginCycle($memReg);
				});
			} catch (EngineControlException $exception) {
            	if($stopEngine($exception))
					goto end_cycle;
            	 elseif($crashEngine($exception)) {
					return;
				} else
					throw $exception;
			}

            if($am instanceof UpdatedAlertManagerInterface)
            	$am->cyclicUpdate( $memReg );

			foreach($this->getPlugins() as $plugin) {
                if($scheduler[$plugin->getIdentifier()] < microtime(true)) {
                    $schedule( $this->getInterval() );

					try {
						$plugin->update($memReg);
					} catch (EngineControlException $exception) {
						if($stopCycle($exception))
							continue 2;
						elseif($stopEngine($exception)) {
							;
						}
						elseif($crashEngine($exception)) {
							return;
						} else
							throw $exception;
					}
                }
            }

			end_cycle:

			array_walk($cycleAwarePlugins, function(CycleAwarePluginInterface $plugin) use ($memReg) {
				$plugin->endCycle($memReg);
			});

			if($memReg instanceof WorkflowDependentMemoryRegister)
				$memReg->endCycle();
        }

		if($memReg instanceof WorkflowDependentMemoryRegister)
			$memReg->tearDown();
    }

	/**
	 * @return int
	 */
	public function getInterval(): int
	{
		return $this->interval;
	}
}