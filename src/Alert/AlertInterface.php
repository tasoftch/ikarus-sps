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

namespace Ikarus\SPS\Alert;


use Ikarus\SPS\Plugin\PluginInterface;

/**
 * Interface AlertInterface
 *
 * Alerts are information processes that can jump out of a normal sps cyclic process.
 * There are three kinds of alerts:
 * notice: The notice is designed just to be logged.
 * warning: A warning should be visible to the user indicating that something might go wrong.
 * critical: A critical alert must inform the user and bring the SPS into a secure emergency state.
 *
 * @package Ikarus\SPS\Alert
 */
interface AlertInterface
{
	const ALERT_LEVEL_NOTICE = 1;
	const ALERT_LEVEL_WARNING = 2;
	const ALERT_LEVEL_CRITICAL = 3;
	const ALERT_LEVEL_EMERGENCY = 4;


	/**
     * An alert identifier
     * Please note that the engine call this method BEFORE setID() to verify the alert's uniquely state.
	 * It must return 0 on the first call.
	 *
     * @return string|int
     */
    public function getID();

	/**
	 * This method must store the alert id and be able to return it under the getID() method.
	 *
	 * @param int $id
	 */
    public function setID(int $id);

    /**
     * Gets the alert code
     *
     * @return int
     */
    public function getCode(): int;

	/**
	 * @return int
	 */
    public function getLevel(): int;

    /**
     * Gets an alert message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Gets an affected plugin if available
     *
     * @return string|PluginInterface|null
     */
    public function getAffectedPlugin();

    /**
     * @return int
     */
    public function getTimeStamp(): int;
}