<?php


namespace Ikarus\SPS\AlertManager\Handler;


use Ikarus\SPS\Alert\AlertInterface;

class AlertConsoleHandler implements HandlerInterface
{
	public $levelNames = [
		AlertInterface::ALERT_LEVEL_NOTICE =>    "[%s] \033[1;31m   Notice",
		AlertInterface::ALERT_LEVEL_WARNING =>   "[%s] \033[0;35m  Warning",
		AlertInterface::ALERT_LEVEL_CRITICAL =>  "[%s] \033[0;31m Critical",
		AlertInterface::ALERT_LEVEL_EMERGENCY => "[%s] \033[0;31mEmergency"
	];


	/**
	 * @inheritDoc
	 */
	public function handleAlert(int $alertID, int $code, int $level, string $message, ?string $affectedPlugins, int $timeStamp): int
	{
		$error = sprintf($this->levelNames[$level] ?? "[%s] \033[0;31mError", (new \DateTime())->format ("[Y-m-d G:i:s.u]: "));

		$error .= sprintf(" (%d): ", $code);
		if($affectedPlugins) $error .= " <$affectedPlugins>";

		$error .= $message . "\033[0m" . PHP_EOL;
		echo $error;
		return self::RETURN_CODE_CONTINUE_NOTIFYING;
	}

	/**
	 * @inheritDoc
	 */
	public function getAcknowledgedAlerts(): array
	{
		return [];
	}
}