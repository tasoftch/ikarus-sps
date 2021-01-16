<?php


namespace Ikarus\SPS\Plugin\ValueProvider;


use Ikarus\SPS\Register\MemoryRegisterInterface;

class CachedValueProviderPlugin extends ValueProviderPlugin
{
	private $cache = [];

	/**
	 * @inheritDoc
	 */
	public function update(MemoryRegisterInterface $memoryRegister)
	{
		foreach($this->getValues() as $key => $value) {
			if($value instanceof ValueProviderInterface)
				$v = $value->getValue( $key );
			elseif($value instanceof ValueInterface)
				$v = $value->getValue();
			elseif(is_callable($value))
				$v = call_user_func($value);
			else
				$v = $value;

			$c = $this->cache[$key] ?? NULL;
			if($c !== $v)
				$memoryRegister->putValue($v, $key, $this->getDomain());
			$this->cache[$key] = $v;
		}
	}
}