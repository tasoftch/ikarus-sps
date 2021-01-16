<?php


namespace Ikarus\SPS;


interface EngineDependencyInterface
{
	/**
	 * This method gets called during start phase of the sps engine setting a reference to the instance or during the tear down phase, passing NULL.
	 *
	 * @param EngineInterface|null $engine
	 */
	public function setEngine(?EngineInterface $engine);
}