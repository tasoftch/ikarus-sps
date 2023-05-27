<?php


namespace Ikarus\SPS\Register;

use Ikarus\SPS\Alert\AlertInterface;

/**
 * A register is responsible that plugins are able to communicate between each other.
 * On a simple Ikarus SPS, all plugins are running in the same process, but if not, a memory register is required.
 *
 *
 * @package Ikarus\SPS\Register
 */
interface MemoryRegisterInterface
{
	/** @var int Marks identified brick to be ready for status */
	const STATUS_REGISTER = 0;

	/** @var int  */
	const STATUS_OFF = 1<<0;
	const STATUS_ON = 1<<1;
	const STATUS_ERROR = 1<<2;

	/** @var int Marks brick manual off state */
	const STATUS_MANUAL = 1<<3;

	/** @var int marks brick manual on state */
	const STATUS_MANUAL_ON = 1<<4;

	const STATUS_PANEL = 1<<5;

	const STATUS_RESERVED_1 = 1<<6;
	const STATUS_RESERVED_2 = 1<<7;

	/**
	 * Stops the current cycle and restart it
	 *
	 * @param int $code
	 * @param string $reason
	 */
	public function stopCycle(int $code = 0, string $reason = "");

	/**
	 * Stops Ikarus SPS and shut it down
	 *
	 * @param int $code
	 * @param string $reason
	 */
	public function stopEngine(int $code = 0, string $reason = "");

	/**
	 * Puts a command to the memory register.
	 * Using the getCommand method, the second parameter passed here gets returned.
	 * Please note that only serializable values are accepted as command information.
	 *
	 * @param string $command
	 * @param null $info
	 * @return void
	 */
	public function putCommand(string $command, $info = NULL);

	/**
	 * Checks if a specific command was set in the memory register or if $command is NULL checks if any command is registered.
	 *
	 * @param string|NULL $command
	 * @return bool
	 */
	public function hasCommand(string $command = NULL): bool;

	/**
	 * Gets the info about a command
	 *
	 * @param string $command
	 * @return mixed
	 */
	public function getCommand(string $command);

	/**
	 * Removes a specific command or all commands from the memory.
	 *
	 * @param string|NULL $command
	 * @return void
	 */
	public function clearCommand(string $command = NULL);

	/**
	 * Puts a value into the memory register.
	 * Values are grouped by domains. It is recommended to group them in as smallest chunks possible.
	 * This might increase the performance of your Ikarus SPS.
	 *
	 * Please note that the values must be serializable.
	 *
	 * @param mixed $value
	 * @param string $key
	 * @param string $domain
	 * @param bool $merged
	 * @return void
	 */
	public function putValue($value, string $key, string $domain, bool $merged = false);

	/**
	 * Checks, if a value exists in a domain or specific of a key
	 *
	 * @param string $domain
	 * @param string|null $key
	 * @return bool
	 */
	public function hasValue(string $domain, $key = NULL): bool;

	/**
	 * Fetches a value from a domain, filtered by a key if specified.
	 *
	 * @param string $domain
	 * @param string|null $key
	 * @return mixed
	 */
	public function fetchValue(string $domain, $key = NULL);

	/**
	 * Sets a status for the defined plugin.
	 * The four defined status are builtin and should not be used for anything else.
	 * But you can define further status codes if required.
	 *
	 * Each plugin must verify, that it exposes its status to the memory.
	 *
	 * Merging status means, that only on, off and error gets applied in the register.
	 *
	 * @param int $status
	 * @param string $pluginID
	 * @param bool $merge
	 * @see MemoryRegisterInterface::STATUS_* constants
	 */
	public function setStatus(int $status, string $pluginID, bool $merge = true);

	/**
	 * If the plugin is available, return its status.
	 *
	 * @param string $pluginID
	 * @return int|null
	 */
	public function getStatus(string $pluginID): ?int;

    /**
     * @param array $panel
     * @param string $pluginID
     */
	public function putPanel(array $panel, string $pluginID);

	/**
	 * Triggers an alert in the sps.
	 *
	 * @param AlertInterface $alert
	 */
	public function triggerAlert(AlertInterface $alert);

	/**
	 * Acknowledge a critical alert and resumes to get back to normal processing.
	 * This method gets called by the alert management, always in the main process.
	 *
	 * @param int $alertID
	 * @return bool
	 */
	public function acknowledgeAlert(int $alertID): bool;

	/**
	 * Some kinds of memory register needs to have all bricks registered.
	 * Normally this is done while compiling the Ikarus SPS. If your application adds a dynamic brick after launching,
	 * it should be registered using this method.
	 *
	 * @param string $brick
	 * @param string $domain
	 */
	public function registerBrick(string $brick, string $domain);
}