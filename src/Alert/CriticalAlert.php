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

/**
 * Class CriticalAlert let Ikarus SPS enter into a secure emergency state, informing the user and wait his decision.
 * A critical alert must give the SPS a chance to get back to normal by calling a resume function.
 * @package Ikarus\SPS\Alert
 */
class CriticalAlert extends AbstractAlert
{
	/** @var callable */
	protected $callback;

	/**
	 * CriticalAlert constructor.
	 *
	 * Pass a resume callback that will restore the normal workflow that Ikarus SPS will be able to exit the emergency state.
	 *
	 * @param int $code
	 * @param string $message
	 * @param callable $resumeCallback
	 * @param null $affectedPlugin
	 * @param mixed ...$args
	 */
	public function __construct(int $code, string $message, callable $resumeCallback, $affectedPlugin = NULL, ...$args)
	{
		parent::__construct($code, $message, $affectedPlugin, $args);
		$this->callback = $resumeCallback;
	}

	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 * @return CriticalAlert
	 */
	public function setCallback(callable $callback): CriticalAlert
	{
		$this->callback = $callback;
		return $this;
	}

	public function getLevel(): int
	{
		return self::ALERT_LEVEL_CRITICAL;
	}
}