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
 * ProcessTest.php
 * ikarus-sps
 *
 * Created on 2019-12-06 16:29 by thomas
 */

use Ikarus\SPS\Helper\Process;
use PHPUnit\Framework\TestCase;

class ProcessTest extends TestCase
{
    private $check;

    private function readProcessIDs() {
        exec("ps aux | grep php", $output);
        $processes = [];
        foreach($output as $line) {
            if(preg_match("/^\w+\s+(\d+)/i", $line, $ms)) {
                $processes[] = $ms[1] * 1;
            }
        }

        return $processes;
    }

    public function testProcessForkWaitChild() {
        $proc = new Process(function() {
            sleep(1);
            // Maintains Hello!
            echo $this->check;

            // Has no effect to the parent process!
            $this->check = 'Thomas';
        });

        $this->check = "Hello";
        $proc->run();
        $this->check = "World";

        $processes = $this->readProcessIDs();

        $this->assertContains( $proc->getProcessID(), $processes );
        $this->assertContains( $proc->getChildProcessID(), $processes );

        $date = new DateTime();

        $proc->wait();

        $processes = $this->readProcessIDs();


        $this->assertContains( $proc->getProcessID(), $processes );
        $this->assertNotContains( $proc->getChildProcessID(), $processes );

        $diff = $date->diff( new DateTime() );
        $this->assertEquals(1, $diff->s);
        $this->assertGreaterThan(0, $diff->f);

        // Because the child task does not output in the same process memory scope.
        $this->assertEmpty($this->getActualOutput());

        $this->assertEquals("World", $this->check);
    }

    public function testProcessWithoutWait() {
        $process = new Process(function(){});
        $process->run();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        // $process->wait();
        usleep(100000);

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertNotContains( $process->getChildProcessID(), $processes );
    }

    public function testProcessDataCommunication() {
        // Please note that you should not pass the parent process into the closure:
        $process = new Process(function() use (&$process) {
            // Don't do this!
            // Because $process is here a copy of the parent process!
        });

        $process = new Process(function(Process $proc) {
            $value = $proc->receiveData( true );
            $value *= 10;
            $proc->sendData($value);
        });

        $process->run();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        usleep(100000);

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        $process->sendData(12);
        $value = $process->receiveData(true);

        $process->wait();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertNotContains( $process->getChildProcessID(), $processes );

        $this->assertEquals(120, $value);
    }

    public function testKillChildProcessFromParent() {
        $process = new Process(function(){
            sleep(10);
        });

        $process->run();
        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        $process->kill();
        $process->wait();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertNotContains( $process->getChildProcessID(), $processes );
    }

    public function testKillProcessFromChild() {
        $process = new Process(function(Process $proc){
            sleep(1);
            $proc->kill();
            sleep(1);
        });

        $date = new DateTime();

        $process->run();
        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        $process->wait();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertNotContains( $process->getChildProcessID(), $processes );

        $diff = $date->diff( new DateTime() );
        $this->assertEquals(1, $diff->s);
        $this->assertGreaterThan(0, $diff->f);
    }

    public function testTrapSignals() {
        $process = new Process(function(){
            // Please note to pack code that you want to trap signals into declare blocks.
            // Also, use as less code as possible in the block, because declaring ticks costs performance!
            declare(ticks=1) {
                sleep(10);
            }
        });

        $process->setTrappedSignals( [SIGINT] );

        $process->run();

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertContains( $process->getChildProcessID(), $processes );

        $process->kill();
        $status = $process->wait();

        $this->assertEquals(SIGINT, pcntl_wexitstatus( $status ));

        $processes = $this->readProcessIDs();

        $this->assertContains( $process->getProcessID(), $processes );
        $this->assertNotContains( $process->getChildProcessID(), $processes );
    }
}
