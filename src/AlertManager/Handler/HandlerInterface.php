<?php


namespace Ikarus\SPS\AlertManager\Handler;


interface HandlerInterface
{
	const RETURN_CODE_STOP_NOTIFYING = 0;
	const RETURN_CODE_CONTINUE_NOTIFYING = 1;


	/**
	 * Notify the client or log (or whatever) that an alert was triggered during a cycle.
	 *
	 * @param int $alertID
	 * @param int $code
	 * @param int $level
	 * @param string $message
	 * @param string|null $affectedPlugins
	 * @param int $timeStamp
	 * @return int
	 */
	public function handleAlert(int $alertID, int $code, int $level, string $message, ?string $affectedPlugins, int $timeStamp): int;

	/**
	 * @param int $alertID
	 * @return void
	 */
	public function acknowledgeAlert(int $alertID);

	/**
	 * If the user did acknowledge the alert, inform back to the SPS core.
	 * This method gets called every cycle, so it must be efficient and may not block or delay the process.
	 *
	 * @return int[]
	 */
	public function getAcknowledgedAlerts(): array;
}