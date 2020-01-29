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


use Ikarus\SPS\Helper\CyclicPluginManager;
use Ikarus\SPS\Plugin\Cyclic\CyclicPluginInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use TASoft\Collection\PriorityCollection;
use TASoft\Util\ValueInjector;

class CyclicEngine extends AbstractEngine implements CyclicEngineInterface
{
    /** @var int */
    private $frequency;
    /** @var PriorityCollection */
    private $cyclicPlugins;


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

    function runEngine()
    {
        $manager = new CyclicPluginManager();

        $scheduler = [];

        $_TS = microtime(true);

        /** @var CyclicPluginInterface $plugin */
        foreach($this->cyclicPlugins->getOrderedElements() as $plugin) {
            $scheduler[ $plugin->getIdentifier() ] = $_TS;
        }

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

        while ($this->isRunning()) {
            $waitFor = min(array_values($scheduler)) - microtime(true);
            if($waitFor > 0)
                usleep($waitFor * 1e6);

            foreach($this->cyclicPlugins->getOrderedElements() as $plugin) {
                if($scheduler[$plugin->getIdentifier()] < microtime(true)) {
                    $schedule( $this->getFrequency() );
                    $plugin->update($manager);
                    if(!$this->isRunning())
                        break;
                }
            }
        }
    }


    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }
}