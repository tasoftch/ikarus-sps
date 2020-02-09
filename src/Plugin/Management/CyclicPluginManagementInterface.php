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

namespace Ikarus\SPS\Plugin\Management;


interface CyclicPluginManagementInterface extends PluginManagementInterface
{
    /**
     * Gets the update frequency in Hz, so 1 means once every second, 50 means 50 times per second.
     * PLEASE NOTE: The frequency does not include the runtime!
     *
     * @return int
     */
    public function getFrequency(): int;

    /**
     * This method can be called to change the frequency temporary for the next interval.
     * So may be your sps run with a 4Hz frequency (interval of 0.25 seconds), but the plugin needs temporary a faster update,
     * it can require a higher frequency.
     *
     * Passing NULL resets the frequency to the engine's default.
     *
     * @param int|null $otherFrequency
     * @return bool
     */
    public function requireTemporaryFrequency(int $otherFrequency = NULL): bool;



    // Intercommunication between cyclic plugins

    /**
     * Puts a command to the cycle stack
     *
     * @param string $command
     * @param null $info
     * @return void
     */
    public function putCommand(string $command, $info = NULL);

    /**
     * Checks if a specific command is on stack or if $command is NULL checks if any command is in stack.
     *
     * @param string|NULL $command
     * @return bool
     */
    public function hasCommand(string $command = NULL): bool;

    /**
     * Gets the info of a command
     *
     * @param string $command
     * @return mixed
     */
    public function getCommand(string $command);

    /**
     * Removes a specific command or all commands from stack.
     *
     * @param string|NULL $command
     * @return void
     */
    public function clearCommand(string $command = NULL);

    /**
     * Puts a value for a specific key in a domain
     *
     * @param $value
     * @param $key
     * @param null $domain
     * @return void
     */
    public function putValue($value, $key, $domain = NULL);

    /**
     * @param $key
     * @param null $domain
     * @return bool
     */
    public function hasValue($key, $domain = NULL): bool;

    /**
     * @param $key
     * @param null $domain
     * @return mixed
     */
    public function fetchValue($key, $domain = NULL);
}