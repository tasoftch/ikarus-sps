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
 * TriggeredEngineTest.php
 * ikarus-sps
 *
 * Created on 2020-01-29 17:34 by thomas
 */

use Ikarus\SPS\Plugin\Listener\CallbackListenerPlugin;
use Ikarus\SPS\Plugin\Management\TriggeredPluginManagementInterface;
use Ikarus\SPS\Plugin\Trigger\CallbackTriggerPlugin;
use Ikarus\SPS\Plugin\Trigger\StopEngineAfterIntervalPlugin;
use Ikarus\SPS\Plugin\Trigger\StopEngineAtDatePlugin;
use Ikarus\SPS\TriggeredEngine;
use PHPUnit\Framework\TestCase;

class TriggeredEngineTest extends TestCase
{
    private $timer;

    protected function startTimer() {
        $this->timer = microtime(true);
    }

    protected function assertBetween($min, $max, $actual) {
        $this->assertGreaterThan($min, $actual);
        $this->assertLessThan($max, $actual);
    }

    protected function stopTimer() {
        return microtime(true) - $this->timer;
    }

    public function testEnginePluginRegistration() {
        $engine = new TriggeredEngine("Ikarus");
        $this->assertEquals("Ikarus", $engine->getName());

        $this->assertTrue( $engine->addPlugin($p1 = new CallbackTriggerPlugin(function() {

        }), 13 ));

        $this->assertTrue( $engine->addPlugin($p2 = new CallbackListenerPlugin(function($evtName, $evt) {

        }, ["EVENT.NAME"]), 3) );

        $this->assertSame([$p2, $p1], $engine->getPlugins());

        $engine->removePlugin( $p1 );
        $this->assertSame([$p2], $engine->getPlugins());
    }

    public function testRunAndStop() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new CallbackTriggerPlugin(function(TriggeredPluginManagementInterface $management) {
            $management->stopEngine(13, "Error");
        }) );

        $this->assertEquals(13, $engine->run());
        $this->assertEquals("Error", $engine->getExitReason());
    }

    public function testAutoStopAfterInterval() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new StopEngineAfterIntervalPlugin(0.3) );

        $this->startTimer();
        $engine->run();
        $this->assertBetween(0.3, 0.31, $this->stopTimer());
    }

    public function testAutoStopAtDate() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new StopEngineAtDatePlugin( $d = new DateTime("now +1second") ) );
        $this->startTimer();
        $this->assertEquals(0, $engine->run());
        $this->assertBetween(1.0, 1.1, $this->stopTimer());
    }

    public function testCleanupHandler() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new CallbackTriggerPlugin( function(TriggeredPluginManagementInterface $management) {
            $management->stopEngine(1, 'Hehe');
        } ) );

        $reached = false;
        $engine->setCleanUpHandler( function() use (&$reached) { $reached = true; } );
        $engine->run();

        $this->assertTrue( $reached );
    }
}
