<?php


namespace Ikarus\SPS\Exception;


class EngineControlException extends SPSException
{
	/** @var int Stops the cycle and repeat */
	const CONTROL_STOP_CYCLE = 1;
	/** @var int stops the engine and tear down after cycle is completed */
	const CONTROL_STOP_ENGINE = 2;
	/** @var int stops the engine immediately (without completing the cycle) */
	const CONTROL_CRASH_ENGINE = 3;

	/** @var int */
	private $control;

	/**
	 * @return int
	 */
	public function getControl(): int
	{
		return $this->control;
	}

	/**
	 * @param int $control
	 * @return static
	 */
	public function setControl(int $control)
	{
		$this->control = $control;
		return $this;
	}
}