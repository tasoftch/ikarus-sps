<?php

namespace Ikarus\SPS\Plugin\Cyclic;


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

class CallbackCyclicPlugin extends AbstractCyclicPlugin
{
    /** @var callable */
    private $callback;

    /**
     * CallbackCyclicPlugin constructor.
     * @param string $identifier
     * @param callable $callback
     */
    public function __construct(string $identifier, callable $callback)
    {
        parent::__construct($identifier);
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