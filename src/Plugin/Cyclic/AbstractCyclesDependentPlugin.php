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

namespace Ikarus\SPS\Plugin\Cyclic;


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

abstract class AbstractCyclesDependentPlugin extends AbstractCyclicPlugin
{
	/** @var int */
	private $cycleInterval = 1;
	private $_current = 0;

	/**
	 * AbstractCyclesDependentPlugin constructor.
	 * @param int $cycleInterval
	 * @param string|null $identifier
	 */
	public function __construct(int $cycleInterval = 1, string $identifier = NULL)
	{
		parent::__construct($identifier);
		$this->cycleInterval = $cycleInterval;
	}

	/**
	 * @inheritDoc
	 */
	public function update(CyclicPluginManagementInterface $pluginManagement)
	{
		if($this->_current <= 0) {
			$this->_current = $this->getCycleInterval();
			$this->updateInterval($pluginManagement);
		} else
			$this->_current--;
	}

	/**
	 * @param CyclicPluginManagementInterface $pluginManagement
	 * @return void
	 */
	abstract protected function updateInterval(CyclicPluginManagementInterface $pluginManagement);

	/**
	 * @return int
	 */
	public function getCycleInterval(): int
	{
		return $this->cycleInterval;
	}

	/**
	 * @param int $cycleInterval
	 * @return static
	 */
	public function setCycleInterval(int $cycleInterval)
	{
		$this->cycleInterval = $cycleInterval;
		return $this;
	}
}