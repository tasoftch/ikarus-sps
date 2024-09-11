<?php


namespace Ikarus\SPS\AlertManager;

use Ikarus\SPS\Register\MemoryRegisterInterface;

/**
 * An alert manager is responsible that alerts are correctly logged and the user gets informed.
 *
 * @package Ikarus\SPS\AlertManager
 */
interface AlertManagerInterface
{
	/**
	 * The originally triggered alerts by plugins are probably not available. This happens if you use a common management register.
	 * Then the plugins might not run in the same process, so they are only able to communicate by the memory register.
	 * The builtin Ikarus Memory Register knows which plugin in which process triggered an alert an is able to send the recovery call to the original plugin.
	 *
	 * This method gets called by the memory register management.
	 *
	 * @param int $alertID
	 * @param int $code
	 * @param int $level
	 * @param string $message
	 * @param string|null $affectedPlugins
	 * @param int $timeStamp
	 */
	public function dispatchAlert(int $alertID, int $code, int $level, string $message, ?string $affectedPlugins, int $timeStamp);

	/**
	 * Backcall to inform a specific alert was acknowledged.
	 *
	 * @param int $alertID
	 * @return void
	 */
	public function acknowledgeAlert(int $alertID);
}