<?php

namespace Ikarus\SPS\Plugin\Trigger;


use Ikarus\SPS\Plugin\PluginManagementInterface;

class CallbackTriggerPlugin extends AbstractEventTriggerPlugin
{
    private $callback;

    public function __construct(callable $calback, $eventName = "")
    {
        parent::__construct($eventName);
        $this->callback = $calback;
    }

    public function run(PluginManagementInterface $manager)
    {
        call_user_func($this->callback, $manager);
    }
}