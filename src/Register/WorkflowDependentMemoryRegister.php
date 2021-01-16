<?php


namespace Ikarus\SPS\Register;


interface WorkflowDependentMemoryRegister extends MemoryRegisterInterface
{
	/**
	 * Called before the SPS will enter into the cycles.
	 */
	public function setup();

	/**
	 * Called before stopping Ikarus SPS
	 */
	public function tearDown();

	/**
	 * Called on each cycle's start.
	 */
	public function beginCycle();

	/**
	 * Called at end of each cycle.
	 */
	public function endCycle();
}