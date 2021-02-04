<?php


namespace Ikarus\SPS\Plugin\ValueProvider;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface;

class ValueProviderPlugin extends AbstractPlugin
{
	private $values;
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

	/**
	 * @inheritDoc
	 */
	public function update(MemoryRegisterInterface $memoryRegister)
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

			$memoryRegister->putValue($v, $key, $this->getDomain());
		}
	}
}