<?php


namespace Ikarus\SPS\Plugin;


use Ikarus\SPS\Register\MemoryRegisterInterface;

abstract class AbstractPlugin implements PluginInterface
{
	/** @var string */
	private $identifier;
	/** @var null|string */
	private $domain;

	public static $defaultDomain = 'ikarus-default-domain';

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


	public static function isStatusOn(int $status): bool {
		if($status & MemoryRegisterInterface::STATUS_ERROR)
			return false;

		if($status & MemoryRegisterInterface::STATUS_MANUAL_ON)
			return true;

		if($status & MemoryRegisterInterface::STATUS_MANUAL)
			return false;

		return (bool)($status & MemoryRegisterInterface::STATUS_ON);
	}

	public static function isStatusOff(int $status): bool {
		if($status & MemoryRegisterInterface::STATUS_ERROR)
			return true;

		if($status & MemoryRegisterInterface::STATUS_MANUAL_ON)
			return false;
		if($status & MemoryRegisterInterface::STATUS_MANUAL)
			return true;

		return ($status & MemoryRegisterInterface::STATUS_OFF) && (0 == ($status & MemoryRegisterInterface::STATUS_ON));
	}

	public static function isStatusManual(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_MANUAL_ON || $status & MemoryRegisterInterface::STATUS_MANUAL;
	}

	public static function isStatusError(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_ERROR;
	}

	public static function isStatusPanel(int $status): bool {
		return $status & MemoryRegisterInterface::STATUS_PANEL;
	}
}