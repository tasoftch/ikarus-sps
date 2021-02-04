<?php


namespace Ikarus\SPS\AlertManager\Handler;


use Ikarus\SPS\Alert\AlertInterface;

class AlertLoggerHandler implements HandlerInterface
{
	/** @var string */
	private $filename;

	public $levelNames = [
		AlertInterface::ALERT_LEVEL_NOTICE =>    '   Notice',
		AlertInterface::ALERT_LEVEL_WARNING =>   '  Warning',
		AlertInterface::ALERT_LEVEL_CRITICAL =>  ' Critical',
		AlertInterface::ALERT_LEVEL_EMERGENCY => 'Emergency'
	];

	/**
	 * AlertLoggerPlugin constructor.
	 * @param string $filename
	 */
	public function __construct(string $filename)
	{
		$this->filename = $filename;
	}

	/**
	 * @inheritDoc
	 */
	public function handleAlert(int $alertID, int $code, int $level, string $message, ?string $affectedPlugins, int $timeStamp): int
	{
		$fh = fopen($this->getFilename(), 'a+');
		$error = (new \DateTime())->format ("[Y-m-d G:i:s.u]: ");

		$error .= $this->levelNames[$level] ?? 'Error';

		$error .= sprintf(" (%d): ", $code);
			if($affectedPlugins) $error .= " <$affectedPlugins>";

		$error .= $message . PHP_EOL;
		fwrite($fh, $error);
		fclose($fh);
		return self::RETURN_CODE_CONTINUE_NOTIFYING;
	}

	/**
	 * @inheritDoc
	 */
	public function getAcknowledgedAlerts(): array
	{
		return [];
	}

	/**
	 * @return string
	 */
	public function getFilename(): string
	{
		return $this->filename;
	}
}