<?php


namespace Ikarus\SPS\Register;


use Ikarus\SPS\Alert\AlertInterface;
use Ikarus\SPS\Alert\CriticalAlert;
use Ikarus\SPS\Alert\EmergencyAlert;
use Ikarus\SPS\EngineDependencyInterface;
use Ikarus\SPS\EngineInterface;
use Ikarus\SPS\Exception\EngineControlException;
use Ikarus\SPS\Plugin\PluginInterface;

class InternalMemoryRegister implements MemoryRegisterInterface, EngineDependencyInterface
{
	private $commands = [];
	private $values = [];
	private $status = [];
	private $alerts = [];

	public static $alertCounter = 10;

	/** @var EngineInterface */
	private $engine;

	/**
	 * @inheritDoc
	 */
	public function setEngine(?EngineInterface $engine)
	{
		$this->engine = $engine;
	}

	/**
	 * @inheritDoc
	 */
	public function stopCycle(int $code = 0, string $reason = "")
	{
		throw (new EngineControlException($reason, $code))->setControl( EngineControlException::CONTROL_STOP_CYCLE );
	}

	/**
	 * @inheritDoc
	 */
	public function stopEngine(int $code = 0, string $reason = "")
	{
		throw (new EngineControlException($reason, $code))->setControl( EngineControlException::CONTROL_STOP_ENGINE );
	}


	/**
	 * @inheritDoc
	 */
	public function putCommand(string $command, $info = NULL)
	{
		$this->commands[$command] = $info;
	}

	/**
	 * @inheritDoc
	 */
	public function hasCommand(string $command = NULL): bool
	{
		return NULL !== $command ? array_key_exists($command, $this->commands) : !empty($this->commands);
	}

	/**
	 * @inheritDoc
	 */
	public function getCommand(string $command)
	{
		return $this->commands[$command] ?? NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function clearCommand(string $command = NULL)
	{
		if($command)
			unset($this->commands[$command]);
		else
			$this->commands = [];
	}

	/**
	 * @inheritDoc
	 */
	public function putValue($value, $key, $domain, bool $merged = false)
	{
		if(NULL === $value)
			unset($this->values[$domain][$key]);
		elseif($merged && is_array($value) && is_array($this->values[$domain][$key]))
			$this->values[$domain][$key] = array_merge($this->values[$domain][$key], $value);
		else
			$this->values[$domain][$key] = $value;
	}

	/**
	 * @inheritDoc
	 */
	public function hasValue($domain, $key = NULL): bool
	{
		if($key !== NULL)
			return isset($this->values[$domain][$key]);
		return isset($this->values[$domain]);
	}

	/**
	 * @inheritDoc
	 */
	public function fetchValue($domain, $key = NULL)
	{
		if($key !== NULL)
			return $this->values[$domain][$key] ?? NULL;
		return $this->values[$domain] ?? NULL;
	}

	/**
	 * @inheritDoc
	 */
	public function triggerAlert(AlertInterface $alert)
	{
		if(0 == $alert->getID())
			$alert->setID( static::$alertCounter++ );

		$this->engine->getAlertManager()->dispatchAlert(
			$alert->getID(),
			$alert->getCode(),
			$alert->getLevel(),
			$alert->getMessage(),
			$alert->getAffectedPlugin() instanceof PluginInterface ? $alert->getAffectedPlugin()->getIdentifier() : $alert->getAffectedPlugin(),
			$alert->getTimeStamp()
		);

		if($alert instanceof CriticalAlert || $alert instanceof EmergencyAlert) {
			if(!isset($this->alerts[ $alert->getID() ])) {
				$this->alerts[$alert->getID()] = $alert;
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function acknowledgeAlert(int $alertID): bool
	{
		/** @var CriticalAlert $alert */
		if($alert = $this->alerts[$alertID] ?? NULL) {
			call_user_func($alert->getCallback());
		}
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function setStatus(int $status, string $pluginID, bool $merge = true)
	{
		if(isset($this->status[$pluginID]) || $status == self::STATUS_REGISTER)
			$this->status[$pluginID] = $merge ? (($this->status[$pluginID] & ~ 7) | ($status & 0x7)) : $status;
	}

	public function putPanel(array $panel, string $pluginID)
	{
		// This method is only available on common memory registers.
	}


	/**
	 * @inheritDoc
	 */
	public function getStatus(string $pluginID): ?int
	{
		return $this->status[$pluginID] ?? NULL;
	}
}