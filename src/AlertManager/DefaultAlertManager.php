<?php


namespace Ikarus\SPS\AlertManager;


use Ikarus\SPS\Alert\AlertInterface;
use Ikarus\SPS\AlertManager\Handler\HandlerInterface;
use Ikarus\SPS\EngineDependencyInterface;
use Ikarus\SPS\EngineInterface;
use Ikarus\SPS\Register\MemoryRegisterInterface;

class DefaultAlertManager implements UpdatedAlertManagerInterface, EngineDependencyInterface
{
	private $notifiers = [];
	private $emergencyAlert = 0;


	/** @var EngineInterface */
	private $engine;

	/**
	 * @param EngineInterface|null $engine
	 */
	public function setEngine(?EngineInterface $engine)
	{
		$this->engine = $engine;
	}


	/**
	 * DefaultAlertManager constructor.
	 * @param HandlerInterface ...$notifiers
	 */
	public function __construct(...$notifiers)
	{
		foreach($notifiers as $notifier)
			$this->addNotifier($notifier);
	}

	/**
	 * @param HandlerInterface $notifier
	 * @return static
	 */
	public function addNotifier(HandlerInterface $notifier) {
		$this->notifiers[] = $notifier;
		return $this;
	}

	/**
	 * @param HandlerInterface $notifier
	 * @return static
	 */
	public function removeNotifier(HandlerInterface $notifier) {
		if(($idx = array_search($notifier, $this->notifiers)) !== false)
			unset($this->notifiers[$idx]);
		return $this;
	}

	/**
	 * @return HandlerInterface[]
	 */
	public function getNotifiers(): array {
		return $this->notifiers;
	}

	/**
	 * @inheritDoc
	 */
	public function dispatchAlert(int $alertID, int $code, int $level, string $message, ?string $affectedPlugins, int $timeStamp)
	{
		foreach($this->getNotifiers() as $notifier) {
			if($level == AlertInterface::ALERT_LEVEL_EMERGENCY)
				$this->emergencyAlert = [$alertID, $code, $message];

			if($notifier->handleAlert($alertID, $code, $level, $message, $affectedPlugins, $timeStamp) == $notifier::RETURN_CODE_STOP_NOTIFYING)
				break;
		}
	}

	public function acknowledgeAlert(int $alertID)
	{
		foreach($this->getNotifiers() as $notifier) {
			$notifier->acknowledgeAlert($alertID);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function cyclicUpdate(MemoryRegisterInterface $memoryRegister)
	{
		if($ea = $this->emergencyAlert) {
			list($aid, $c, $m) = $ea;

			$memoryRegister->acknowledgeAlert($aid);
			$this->engine->stop($c, $m);
			return;
		}
		foreach($this->getNotifiers() as $notifier) {
			if($ack = $notifier->getAcknowledgedAlerts())
				array_walk($ack, function($a) use ($memoryRegister) { $memoryRegister->acknowledgeAlert($a); });
		}
	}
}