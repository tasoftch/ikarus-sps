<?php

namespace Ikarus\SPS\Plugin\Trigger;


use Ikarus\SPS\Plugin\Management\TriggeredPluginManagementInterface;

class CallbackTriggerPlugin extends AbstractEventTriggerPlugin
{
    private $callback;

    public function __construct(callable $calback, $eventName = "")
    {
        parent::__construct($eventName);
        $this->callback = $calback;
    }

    public function run(TriggeredPluginManagementInterface $manager)
    {
        call_user_func($this->callback, $manager);
    }
}