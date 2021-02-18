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
}