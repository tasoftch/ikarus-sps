<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 07/03/20
 * Time: 13:29
 */

namespace Ikarus\SPS\Plugin\Management;

/**
 * The plugin management observer allows you to interact directly on changes of the pluginmanagement.
 *
 * Interface PluginManagementObserverInterface
 * @package Ikarus\SPS\Plugin\Management
 */
interface PluginManagementObserverInterface
{
    const DOMAIN_KEY = 'domain';
    const VALUE_KEY = 'value';
    const KEY_KEY = 'key';
    const ALERT_KEY = 'alert';
    const COMMAND_KEY = 'command';

    /**
     * Triggered by the management on a change that affects the observer
     *
     * @param array $changes
     */
    public function trigger(array $changes);

    /**
     * Called for each observer to determine if the observer should be triggered.
     *
     * @param array $changes
     * @return bool
     */
    public function shouldTrigger(array $changes): bool;
}