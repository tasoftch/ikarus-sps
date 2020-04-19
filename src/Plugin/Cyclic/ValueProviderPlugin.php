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


use Ikarus\SPS\Plugin\Cyclic\Value\ValueInterface;
use Ikarus\SPS\Plugin\Cyclic\Value\ValueProviderInterface;
use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

class ValueProviderPlugin extends AbstractCyclicPlugin
{
	private $values = [];
	private $domain;

	/**
	 * ValueProviderPlugin constructor.
	 * @param string $identifier
	 * @param string $domain
	 * @param callable[]|string[]|ValueInterface[]|ValueProviderInterface[] $values
	 */
	public function __construct(string $identifier, string $domain, iterable $values = [])
	{
		parent::__construct($identifier);

		$this->values = $values;
		$this->domain = $domain;
	}

	/**
	 * @inheritDoc
	 */
	public function update(CyclicPluginManagementInterface $pluginManagement)
	{
		foreach($this->values as $key => $value) {
			if($value instanceof ValueProviderInterface)
				$v = $value->getValue( $key );
			elseif($value instanceof ValueInterface)
				$v = $value->getValue();
			elseif(is_callable($value))
				$v = call_user_func($value);
			else
				$v = $value;

			$pluginManagement->putValue($v, $key, $this->getDomain());
		}
	}

	/**
	 * @param string $key
	 * @param mixed|ValueInterface|ValueProviderInterface|callable $value
	 * @return static
	 */
	public function addValue(string $key, $value) {
		$this->values[$key] = $value;
		return $this;
	}

	/**
	 * @param string $key
	 * @return static
	 */
	public function removeValue(string $key) {
		unset($this->values[$key]);
		return $this;
	}

	/**
	 * @return iterable
	 */
	public function getValues(): iterable
	{
		return $this->values;
	}

	/**
	 * @return string
	 */
	public function getDomain(): string
	{
		return $this->domain;
	}
}