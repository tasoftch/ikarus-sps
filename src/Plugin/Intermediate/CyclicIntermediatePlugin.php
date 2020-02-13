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


use Ikarus\SPS\Plugin\Cyclic\CyclicPluginInterface;
use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;
use Ikarus\SPS\Plugin\Management\PluginManagementInterface;

class CyclicIntermediatePlugin extends AbstractIntermediatePlugin implements CyclicPluginInterface
{
    const CMD_FETCH_VALUE = 'fv';
    const CMD_PUT_VALUE = 'pv';

    const CMD_FETCH_COMMAND = 'fc';
    const CMD_EXISTS_COMMAND = 'fe';
    const CMD_PUT_COMMAND = 'pc';

    public function establishConnection()
    {
        if(parent::establishConnection()) {
            socket_set_nonblock($this->socket);
            return true;
        }
        return false;
    }


    protected function doCommand($command, PluginManagementInterface $management): string
    {
        if($management instanceof CyclicPluginManagementInterface) {
            if(preg_match("/^(?:(fv|pv|fc|fe)\s+([a-z0-9\.\-]+)\s*([a-z0-9\.\-]*)\s*(.*)|pc\s+([a-z0-9\.\-]+)\s*(.*))$/i", $command, $ms)) {
                $cmd = strtolower(isset($ms[5]) ? 'pc' : $ms[1]);

                switch ($cmd) {
                    case 'fv':
                        return $management->fetchValue($ms[2], $ms[3] ?: NULL);
                    case 'pv':
                        $management->putValue(unserialize($ms[4]), $ms[3], $ms[2]);
                        return "1";
                    case 'fc':
                        return $management->getCommand($ms[2]);
                    case 'fe':
                        return $management->hasCommand($ms[2]) ? "1" : "0";
                    case 'pc':
                        $management->putCommand($ms[5], unserialize($ms[6]));
                        return "1";
                }
            }
        }
        return "-1";
    }

    public function update(CyclicPluginManagementInterface $pluginManagement)
    {
        $this->establishConnection();
        $this->trapNextCommand($pluginManagement);
    }
}