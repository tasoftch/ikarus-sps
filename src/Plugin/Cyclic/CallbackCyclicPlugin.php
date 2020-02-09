<?php

namespace Ikarus\SPS\Plugin\Cyclic;


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

class CallbackCyclicPlugin extends AbstractCyclicPlugin
{
    /** @var callable */
    private $callback;

    /**
     * CallbackCyclicPlugin constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    public function update(CyclicPluginManagementInterface $pluginManagement)
    {
        return call_user_func($this->getCallback(), $pluginManagement);
    }
}