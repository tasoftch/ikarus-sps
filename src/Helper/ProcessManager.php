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


use Ikarus\SPS\Exception\SignalKillException;
use Ikarus\SPS\Plugin\TearDownPluginInterface;
use Ikarus\SPS\Exception\SPSException;

class ProcessManager
{
    private $mainProcess = true;
    private $processes = [];
    private $plugin;

    public function signalHandler() {
        if($this->plugin instanceof TearDownPluginInterface)
            $this->plugin->tearDown();

        throw new SignalKillException("Process killed");
    }

    /**
     * @return bool
     */
    public function isMainProcess(): bool
    {
        return $this->mainProcess;
    }

    /**
     * This method forks the process and stores the child process.
     * Note, the code after that method call gets performed twice!
     * Once for the main process and once for the child process.
     *
     * @param $plugin
     * @see ProcessManager::isMainProcess()
     */
    public function fork($plugin) {
        $pid = pcntl_fork();
        if($pid > 0) {
            $this->processes[$pid] = $plugin;
            pcntl_signal(SIGINT, [$this, 'signalHandler']);
        } elseif($pid == 0) {
            $this->mainProcess = false;
            $this->plugin = $plugin;
            pcntl_signal(SIGINT, [$this, 'signalHandler']);
        }
        else
            throw new SPSException("Could not fork the process");
    }

    /**
     * Gets the process id of a plugin
     *
     * @param $plugin
     * @return void
     */
    public function killProcessOfPlugin($plugin) {
        if(!$this->isMainProcess())
            throw new SPSException("Can not get process ID from child process context");

        if(($idx = array_search($plugin, $this->processes)) !== false) {
            if(posix_kill($idx, SIGINT)) {
                unset($this->processes[$idx]);
            } else {
                throw new SPSException("Can not get process ID from child process context");
            }
        }
    }

    /**
     * Stops all processes of the triggers
     */
    public function killAll() {
        foreach($this->processes as $pid => $plugin)
            posix_kill($pid, SIGINT);
    }

    /**
     * Waits for all processes to stop
     *
     * @return int
     */
    public function waitForAll() {
        pcntl_wait($status);
        return $status;
    }
}