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


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

class CyclicPluginManager implements CyclicPluginManagementInterface
{
    /** @var callable */
    private $f, $rtf, $se;

    private $commands = [];
    private $values = [];


    public function getFrequency(): int
    {
        return ($this->f)();
    }

    public function requireTemporaryFrequency(?int $otherFrequency = NULL): bool
    {
        return ($this->rtf)($otherFrequency);
    }

    public function stopEngine($code = 0, $reason = ""): bool
    {
        return ($this->se)($code, $reason) ? true : false;
    }

    public function putCommand(string $command, $info = NULL)
    {
        $this->commands[$command] = $info;
    }

    public function hasCommand(string $command = NULL): bool
    {
        return NULL !== $command ? isset($this->commands[$command]) : !empty($this->commands);
    }

    public function getCommand(string $command)
    {
        return $this->commands[$command] ?? NULL;
    }

    public function clearCommand(string $command = NULL)
    {
        if($command)
            unset($this->commands[$command]);
        else
            $this->commands = [];
    }
    public function putValue($value, $key, $domain)
    {
        if(NULL === $value)
            unset($this->values[$domain][$key]);
        else
            $this->values[$domain][$key] = $value;
    }

    public function hasValue($domain, $key = NULL): bool
    {
        if($key !== NULL)
            return isset($this->values[$domain][$key]);
        return isset($this->values[$domain]);
    }

    public function fetchValue($domain, $key = NULL)
    {
        if($key !== NULL)
            return $this->values[$domain][$key] ?? NULL;
        return $this->values[$domain] ?? NULL;
    }
}