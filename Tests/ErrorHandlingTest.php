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
 * ErrorHandlingTest.php
 * ikarus-sps
 *
 * Created on 2019-12-19 13:11 by thomas
 */

use Ikarus\SPS\Event\PluginErrorEvent;
use Ikarus\SPS\Error\Deprecated;
use Ikarus\SPS\Plugin\Trigger\Error\DispatchedEventTriggerErrorHandlerPlugin;
use Ikarus\SPS\Plugin\Trigger\Error\DispatchedFileLoggerErrorHandlerPlugin;
use Ikarus\SPS\Plugin\Trigger\Error\DispatchedIgnoreErrorHandlerPlugin;
use Ikarus\SPS\Error\Fatal;
use Ikarus\SPS\Error\Notice;
use Ikarus\SPS\Error\Warning;
use Ikarus\SPS\Plugin\Listener\CallbackListenerPlugin;
use Ikarus\SPS\Plugin\Trigger\CallbackTriggerPlugin;
use Ikarus\SPS\TriggeredEngine;
use PHPUnit\Framework\TestCase;

class ErrorHandlingTest extends TestCase
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

    public function testIgnoreErrorHandling() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new DispatchedIgnoreErrorHandlerPlugin() );

        $engine->addPlugin( new CallbackTriggerPlugin(function() {
            trigger_error("Warning", E_USER_WARNING);
            trigger_error("Notice", E_USER_NOTICE);
            trigger_error("Deprecated", E_USER_DEPRECATED);
            trigger_error("Error", E_USER_ERROR);
        }) );

        $this->startTimer();
        $engine->run();
        $this->assertBetween(0, 0.1, $this->stopTimer());
    }

    public function testFileLogger() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new DispatchedFileLoggerErrorHandlerPlugin('Tests/test.log') );

        $engine->addPlugin( new CallbackTriggerPlugin(function() {
            trigger_error("Warning", E_USER_WARNING);
            trigger_error("Notice", E_USER_NOTICE);
            trigger_error("Deprecated", E_USER_DEPRECATED);
            trigger_error("Error", E_USER_ERROR);
        }) );

        if(file_exists('Tests/test.log'))
            unlink('Tests/test.log');

        $engine->run();

        $this->assertFileExists('Tests/test.log');

        $this->assertCount( 4, file('Tests/test.log') );
    }

    public function testEventTriggerPluginWithoutListeners() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new DispatchedEventTriggerErrorHandlerPlugin() );

        $engine->addPlugin( new CallbackTriggerPlugin(function() {
            trigger_error("Warning", E_USER_WARNING);
            trigger_error("Notice", E_USER_NOTICE);
            trigger_error("Deprecated", E_USER_DEPRECATED);
            trigger_error("Error", E_USER_ERROR);
        }) );

        $this->startTimer();
        $engine->run();
        $this->assertBetween(0, 0.1, $this->stopTimer());
    }

    public function testEventTriggerPluginWithListener() {
        $engine = new TriggeredEngine();
        $engine->addPlugin( new DispatchedEventTriggerErrorHandlerPlugin() );

        $engine->addPlugin( new CallbackTriggerPlugin(function() {
            trigger_error("Warning", E_USER_WARNING);
            trigger_error("Notice", E_USER_NOTICE);
            trigger_error("Deprecated", E_USER_DEPRECATED);
            trigger_error("Error", E_USER_ERROR);
        }) );

        $engine->addPlugin( new CallbackListenerPlugin(function($name, PluginErrorEvent $event) use (&$classes, &$messages, &$codes, &$files) {
            $err = $event->getError();

            $classes[] = get_class($err);
            $messages[] = $err->getMessage();
            $codes[] = $err->getCode();
            $files[] = $err->getFile();

            // Stopping propagation will cause that the plugin shuts down, but the engine will continue.
            // So its up to the listeners to stop the engine
            // $event->stopPropagation();
        }, ["ERR.WARNING", 'ERR.NOTICE', 'ERR.DEPRECATED', 'ERR.FATAL', 'ERR.EXCEPTION', 'ERR.ERROR']));

        $this->startTimer();
        $engine->run();
        $this->assertBetween(0, 0.1, $this->stopTimer());

        $this->assertEquals([
            Warning::class,
            Notice::class,
            Deprecated::class,
            Fatal::class
        ], $classes);

        $this->assertEquals([
            'Warning',
            "Notice",
            "Deprecated",
            "Error"
        ], $messages);

        $this->assertEquals([
            E_USER_WARNING,
            E_USER_NOTICE,
            E_USER_DEPRECATED,
            E_USER_ERROR
        ], $codes);
    }
}
