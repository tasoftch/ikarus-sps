<?php


namespace Ikarus\SPS\Alert;

/**
 * Receiving an emergency alert, Ikarus SPS will immediately shut down.
 * @package Ikarus\SPS\Alert
 */
class EmergencyAlert extends AbstractAlert
{
	/** @var callable */
	protected $callback;

	/**
	 * EmergencyAlert constructor.
	 *
	 * If required pass a callback that gets performed before Ikarus SPS will shut down the engine.
	 *
	 * @param int $code
	 * @param string $message
	 * @param callable|null $shutDownCallback
	 * @param null $affectedPlugin
	 * @param mixed ...$args
	 */
	public function __construct(int $code, string $message, callable $shutDownCallback = NULL, $affectedPlugin = NULL, ...$args)
	{
		parent::__construct($code, $message, $affectedPlugin, $args);
		$this->callback = $shutDownCallback;
	}

	/**
	 * @return callable|null
	 */
	public function getCallback(): ?callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 * @return EmergencyAlert
	 */
	public function setCallback(callable $callback): EmergencyAlert
	{
		$this->callback = $callback;
		return $this;
	}

	public function getLevel(): int
	{
		return self::ALERT_LEVEL_EMERGENCY;
	}
}