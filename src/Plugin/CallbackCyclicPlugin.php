<?php

namespace Ikarus\SPS\Plugin;


use Ikarus\SPS\Register\MemoryRegisterInterface;

class CallbackCyclicPlugin extends AbstractPlugin
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

    public function update(MemoryRegisterInterface $memoryRegister)
    {
        return call_user_func($this->getCallback(), $memoryRegister);
    }
}