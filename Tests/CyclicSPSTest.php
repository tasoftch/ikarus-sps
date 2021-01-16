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

/**
 * CyclicSPSTest.php
 * ikarus-sps
 *
 * Created on 2020-01-29 16:10 by thomas
 */

use Ikarus\SPS\CyclicEngine;
use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Plugin\StopEngine\StopEngineAfterCycleCountPlugin;
use Ikarus\SPS\Plugin\StopEngine\StopEngineAfterIntervalPlugin;
use Ikarus\SPS\Plugin\StopEngine\StopEngineAtDatePlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface;
use PHPUnit\Framework\TestCase;

class CyclicSPSTest extends TestCase
{
    public function testInitFrequency() {
        $sps = new CyclicEngine(4);

        $sps->addPlugin(new StopEngineAfterIntervalPlugin(1));
        $sps->addPlugin($pl = new MyPlugin('test'));

        $sps->run();

        $this->assertEquals(4, $pl->count);
    }

    public function testStopAtDate() {
        $sps = new CyclicEngine(4);

        $sps->addPlugin(new StopEngineAtDatePlugin(new DateTime("now +1second")));
        $sps->addPlugin($pl = new MyPlugin('test'));

        $sps->run();

        $this->assertEquals(4, $pl->count);
    }


    public function testCycleCounter() {
        $sps = new CyclicEngine(4);

        $sps->addPlugin(new StopEngineAfterCycleCountPlugin(3));
        $sps->addPlugin($pl = new MyPlugin('test'));

        $sps->run();
        $this->assertEquals(3, $pl->count);
    }
}

class MyPlugin extends AbstractPlugin implements PluginInterface {
    public $count = 0;
    public function update(MemoryRegisterInterface $memoryRegister)
    {
        $this->count++;
    }
}
