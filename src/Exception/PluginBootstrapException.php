<?php


namespace Ikarus\SPS\Exception;


use Ikarus\SPS\Plugin\PluginInterface;

class PluginBootstrapException extends SPSException
{
	/** @var PluginInterface */
	private $plugin;

	/**
	 * @param PluginInterface $plugin
	 * @return PluginBootstrapException
	 */
	public function setPlugin(PluginInterface $plugin): PluginBootstrapException
	{
		$this->plugin = $plugin;
		return $this;
	}

	/**
	 * @return PluginInterface
	 */
	public function getPlugin(): PluginInterface
	{
		return $this->plugin;
	}
}