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

namespace Ikarus\SPS\Plugin\Intermediate;


use Ikarus\SPS\Event\ResponseEvent;
use Ikarus\SPS\Plugin\Management\PluginManagementInterface;
use Ikarus\SPS\Plugin\Management\TriggeredPluginManagementInterface;
use Ikarus\SPS\Plugin\Trigger\TriggerPluginInterface;

class TriggerIntermediatePlugin extends AbstractIntermediatePlugin implements TriggerPluginInterface
{
    protected function doCommand($command, PluginManagementInterface $management): string
    {
        if($management instanceof TriggeredPluginManagementInterface) {
            $buf = preg_split("/\s+/i", $command);
            $command = array_shift($buf);

            $management->dispatchEvent( strtoupper($command), new ResponseEvent("Command $command not found"), ...$buf );
            return $management->requestDispatchedResponse()->getResponse();
        }
        return "-1";
    }

    public function run(TriggeredPluginManagementInterface $manager)
    {
        do {
            $this->establishConnection();
            $cmd = $this->trapNextCommand($manager);
        } while ($cmd && !preg_match("/^(quit|stop)/i", $cmd));
    }
}