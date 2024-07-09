<?php


namespace Ikarus\SPS\Plugin;


use Ikarus\SPS\Register\MemoryRegisterInterface;

abstract class AbstractPlugin implements PluginInterface
{
	/** @var string */
	private $identifier;
	/** @var null|string */
	private $domain;

	/** @var MemoryRegisterInterface */
	protected $memoryRegister;

	public static $defaultDomain = 'ikarus-default-domain';

	protected $auto_register = true;

	private $exposed_values = [];

	/**
	 * AbstractPlugin constructor.
	 * For Ikarus SPS compliance always let the first parameter as identifier.
	 *
	 * @param string $identifier
	 * @param string|null $domain
	 */
	public function __construct(string $identifier, string $domain = NULL)
	{
		$this->identifier = $identifier;
		$this->domain = $domain;
	}


	/**
	 * @return string
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	/**
	 * @inheritDoc
	 */
	public function initialize(MemoryRegisterInterface $memoryRegister)
	{
		$this->memoryRegister = $memoryRegister;
		if($this->auto_register) {
			$memoryRegister->registerBrick($this->getIdentifier(), $this->getDomain());
		}
	}

	/**
	 * @return string|null
	 */
	public function getDomain(): string
	{
		return $this->domain ?: static::$defaultDomain;
	}

	/**
	 * @param string|null $domain
	 * @return static
	 */
	public function setDomain(?string $domain)
	{
		$this->domain = $domain;
		return $this;
	}

	/**
	 * Use this method from automation to change the bricks state.
	 *
	 * @param bool|NULL $on
	 * @param bool|NULL $err
	 * @return int
	 */
	protected function statusChange(bool $on = NULL, bool $err = NULL): int {
		$status = $this->memoryRegister->getStatus($this->getIdentifier());
		if($on)
			$status = static::statusEnable($status);
		elseif($on === false)
			$status = static::statusDisable($status);

		if($err)
			$status = static::statusError($status);
		elseif($err === false)
			$status = static::statusErrorRelease($status);
		$this->memoryRegister->setStatus($status, $this->getIdentifier());
		return $status;
	}

	/**
	 * Implements the required behaviour of a plugin on its status.
	 *
	 * @param int $status
	 * @return bool
	 */
	public static function isStatusOn(int $status): bool {
		if($status & MemoryRegisterInterface::STATUS_ERROR)
			return false;

		if($status & MemoryRegisterInterface::STATUS_MANUAL_ON)
			return true;

		if($status & MemoryRegisterInterface::STATUS_MANUAL)
			return false;

		return (bool)($status & MemoryRegisterInterface::STATUS_ON);
	}

	/**
	 * Implements the required behaviour of a plugin on its status.
	 *
	 * @param int $status
	 * @return bool
	 */
	public static function isStatusOff(int $status): bool {
		if($status & MemoryRegisterInterface::STATUS_ERROR)
			return true;

		if($status & MemoryRegisterInterface::STATUS_MANUAL_ON)
			return false;
		if($status & MemoryRegisterInterface::STATUS_MANUAL)
			return true;

		return ($status & MemoryRegisterInterface::STATUS_OFF) && (0 == ($status & MemoryRegisterInterface::STATUS_ON));
	}

	/**
	 * Implements the required behaviour of a plugin on its status.
	 *
	 * @param int $status
	 * @return bool
	 */
	public static function isStatusManual(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_MANUAL_ON || $status & MemoryRegisterInterface::STATUS_MANUAL;
	}

	/**
	 * Implements the required behaviour of a plugin on its status.
	 *
	 * @param int $status
	 * @return bool
	 */
	public static function isStatusError(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_ERROR;
	}

	/**
	 * Implements the required behaviour of a plugin on its status.
	 *
	 * @param int $status
	 * @return bool
	 */
	public static function isStatusPanel(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_PANEL;
	}

	/**
	 * Sets the status to ON
	 *
	 * @param int $status
	 * @return int
	 */
	public static function statusEnable(int $status): int {
		return $status | MemoryRegisterInterface::STATUS_ON;
	}

	/**
	 * Sets the status to OFF
	 *
	 * @param int $status
	 * @return int
	 */
	public static function statusDisable(int $status): int {
		return ($status | MemoryRegisterInterface::STATUS_OFF) & ~MemoryRegisterInterface::STATUS_ON;
	}

	public static function statusError(int $status): int {
		return ($status | MemoryRegisterInterface::STATUS_ERROR);
	}

	public static function statusErrorRelease(int $status): int {
		return ($status & ~MemoryRegisterInterface::STATUS_ERROR);
	}

	public static function statusManualRelease(int $status): int {
		return $status & ~MemoryRegisterInterface::STATUS_MANUAL & ~MemoryRegisterInterface::STATUS_MANUAL_ON;
	}

	// Normalized Access to own values and commands declared in the plugin description

	protected function putCommand($cmd, $info = NULL) {
		$this->memoryRegister->putCommand($this->getDomain() . "." . $this->getIdentifier() . "." . $cmd, $info);
	}

	protected function clearCommand($cmd) {
		$this->memoryRegister->clearCommand($this->getDomain() . "." . $this->getIdentifier() . "." . $cmd);
	}

	protected function getCommand($cmd) {
		$this->memoryRegister->getCommand($this->getDomain() . "." . $this->getIdentifier() . "." . $cmd);
	}

	protected function hasCommand($cmd) {
		$this->memoryRegister->hasCommand($this->getDomain() . "." . $this->getIdentifier() . "." . $cmd);
	}

	protected function putValue($value, string $key) {
		$this->memoryRegister->putValue($value, $this->getIdentifier() . ".$key", $this->getDomain());
	}

	protected function fetchValue(string $key) {
		return $this->memoryRegister->fetchValue($this->getDomain(), $this->getIdentifier() . ".$key");
	}


	protected function registerExposedValue(string $domain, string $key = NULL): ?callable {
		if(!$key)
			list($domain, $key) = explode(".", $domain, 2);

		if($domain && $key) {
			return function($value = NULL) use ($domain, $key) {
				if(func_num_args() < 1)
					return $this->memoryRegister->fetchValue($domain, $value);
				else
					$this->memoryRegister->putValue($value, $key, $domain);
				return $value;
			};
		}
		return NULL;
	}
}