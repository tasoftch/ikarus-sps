<?php


namespace Ikarus\SPS\Plugin\Interval;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface;

abstract class AbstractTimeIntervalPlugin extends AbstractPlugin
{
	private $_current = 0;
	/**
	 * Returns the time in seconds to wait between cycles
	 *
	 * @return int
	 */
	abstract protected function getTime(): int;

	/**
	 * @param MemoryRegisterInterface $memoryRegister
	 * @return void
	 */
	abstract protected function updateInterval(MemoryRegisterInterface $memoryRegister);

	/**
	 * @inheritDoc
	 */
	public function update(MemoryRegisterInterface $memoryRegister)
	{
		if(time() >= $this->_current) {
			$this->_current = time() + $this->getTime();
			$this->updateInterval($memoryRegister);
		}
	}
}