<?php


namespace Ikarus\SPS\Plugin;


use Ikarus\SPS\EngineDependencyInterface;
use Ikarus\SPS\EngineInterface;

abstract class AbstractEngineDependentPlugin extends AbstractPlugin implements EngineDependencyInterface
{
	/** @var EngineInterface|null */
	protected $engine;

	public function setEngine(?EngineInterface $engine)
	{
		$this->engine = $engine;
	}
}