<?php

namespace Ikarus\SPS\Plugin\Trigger;


use Ikarus\SPS\Plugin\AbstractPlugin;

abstract class AbstractEventTriggerPlugin extends AbstractPlugin implements TriggerPluginInterface
{
    private $eventName;

    /**
     * AbstractPlugin constructor.
     * @param $eventName
     */
    public function __construct($eventName = "")
    {
        $this->eventName = $eventName;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param string $eventName
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;
    }
}