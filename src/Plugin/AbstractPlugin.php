<?php


namespace Ikarus\SPS\Plugin;


use Ikarus\SPS\Register\MemoryRegisterInterface;

abstract class AbstractPlugin implements PluginInterface
{
	/** @var string */
	private $identifier;

	/**
	 * AbstractPlugin constructor.
	 * For Ikarus SPS compliance always let the first parameter as identifier.
	 *
	 * @param string $identifier
	 */
	public function __construct(string $identifier)
	{
		$this->identifier = $identifier;
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
}