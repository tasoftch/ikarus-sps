<?php


namespace Ikarus\SPS\Plugin\Interval;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface;

abstract class AbstractMicroTimeIntervalPlugin extends AbstractPlugin
{
	private $_current = 0;
	/**
	 * Returns the time in micro seconds to wait between cycles
	 * Please note that the update interval is never fasten than the given engine frequency.
	 * @return int
	 */
	abstract protected function getMicroTime(): int;

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
		if(microtime(true) >= $this->_current) {
			$this->_current = microtime(true) + $this->getMicroTime();
			$this->updateInterval($memoryRegister);
		}
	}
}