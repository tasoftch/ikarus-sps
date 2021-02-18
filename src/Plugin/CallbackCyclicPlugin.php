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
	 * @param string|null $domain
	 */
    public function __construct(string $identifier, callable $callback, string $domain = NULL)
    {
        parent::__construct($identifier, $domain);
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

	/**
	 * @inheritDoc
	 */
    public function update(MemoryRegisterInterface $memoryRegister)
    {
        return call_user_func($this->getCallback(), $memoryRegister);
    }
}