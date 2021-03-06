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

namespace Ikarus\SPS;

use Ikarus\SPS\AlertManager\AlertManagerInterface;
use Ikarus\SPS\Plugin\PluginInterface;
use Ikarus\SPS\Register\MemoryRegisterInterface;

/**
 * The Ikarus SPS Engine dispatches the plugins to separate processes (if needed) and handle a common memory register to let all plugins communicate between each other.
 * When the SPS Engine runs, it is controlling a machine or what ever.
 *
 * @package Ikarus\SPS
 */
interface EngineInterface
{
    /**
     * Gets all plugins to run
     *
     * @return array|PluginInterface[]
     */
    public function getPlugins(): array;

	/**
	 * @return MemoryRegisterInterface
	 */
	public function getMemoryRegister(): MemoryRegisterInterface;

	/**
	 * @return AlertManagerInterface
	 */
	public function getAlertManager(): AlertManagerInterface;

    /**
     * Runs the engine.
	 * Please note that this method call blocks the current process.
     */
    public function run();

	/**
	 * Stops the engine
	 *
	 * @param int $code
	 * @param string $reason
	 */
	public function stop($code = 0, $reason = "");


}