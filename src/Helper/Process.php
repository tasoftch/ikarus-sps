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

namespace Ikarus\SPS\Helper;


use Ikarus\SPS\Exception\SignalException;
use Ikarus\SPS\Exception\SPSException;
use Ikarus\SPS\Pipe;

class Process
{
    /** @var callable */
    private $callback;
    /** @var int */
    private $processID;
    /** @var int Only for main processes to know their child processes */
    private $_childProcessID = 0;

    /** @var bool */
    private $mainProcess = true;
    /** @var bool  */
    private $running = false;

    /** @var Pipe */
    private $toParentPipe;
    /** @var Pipe */
    private $toChildPipe;

    private $trappedSignals = [];

    /**
     * Process constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        $this->processID = getmypid();
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getProcessID(): int
    {
        return $this->processID;
    }

    /**
     * @return bool
     */
    public function isMainProcess(): bool
    {
        return $this->mainProcess;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * @return int
     */
    public function getChildProcessID(): int
    {
        return $this->_childProcessID;
    }

    /**
     * Runs the process.
     *
     * Calling this method forks the process and establish the communication tunnel between them.
     *
     * @param mixed ...$arguments
     */
    public function run(...$arguments) {
        if($this->isMainProcess() && !$this->isRunning()) {
            // Starts the process
            $this->toParentPipe = new Pipe();
            $this->toChildPipe = new Pipe();

            $pid = pcntl_fork();
            if($pid == -1)
                throw new SPSException("Could not fork process", 300);

            if($pid) {
                $this->_childProcessID = $pid;
            } else {
                $this->mainProcess = false;
                $this->processID = getmypid();

                if($signals = $this->getTrappedSignals()) {
                    foreach($signals as $signal) {
                        pcntl_signal($signal, function($signo) {
                            $e = new SignalException("Signal triggered", -1);
                            $e->setSignal($signo);
                            throw $e;
                        });
                    }
                }
                try {
                    call_user_func_array($this->callback, $arguments);
                } catch (SignalException $exception) {
                    switch ($exception->getSignal()) {
                        case SIGINT:
                        case SIGTERM:
                            exit( $exception->getSignal() );
                    }
                }

                exit();
            }
        }
    }

    /**
     * Kills the child process.
     * Its not important who (parent or child process) calls this method, it will only kill the child process.
     */
    public function kill() {
        if($this->isRunning()) {
            if($this->isMainProcess())
                posix_kill($this->_childProcessID, SIGINT);
            else
                posix_kill($this->processID, SIGINT);
        }
    }

    /**
     * Waits for the child process to be done.
     *
     * If the child process calls this method, nothing happens.
     */
    public function wait() {
        if($this->isMainProcess()) {
            pcntl_waitpid( $this->_childProcessID, $status );
            return $status;
        }
        return 0;
    }

    /**
     * Sends data to the other process.
     * If the child process calls this method, it will send data to the parent process and vice versa.
     *
     * @param $data
     */
    public function sendData($data) {
        if($this->isMainProcess())
            $this->toChildPipe->sendData($data);
        else
            $this->toParentPipe->sendData($data);
    }

    /**
     * Checks if there was data sent by the child or parent process.
     *
     * @param bool $blockThread
     * @return mixed
     */
    public function receiveData(bool $blockThread = false) {
        if($this->isMainProcess()) {
            return $this->toParentPipe->receiveData($blockThread);
        } else {
            return $this->toChildPipe->receiveData($blockThread);
        }
    }

    /**
     * @return array
     */
    public function getTrappedSignals(): array
    {
        return $this->trappedSignals;
    }

    /**
     * @param array $trappedSignals
     */
    public function setTrappedSignals(array $trappedSignals): void
    {
        if(!$this->isRunning())
            $this->trappedSignals = $trappedSignals;
    }
}