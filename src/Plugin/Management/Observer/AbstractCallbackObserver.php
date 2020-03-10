<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 07/03/20
 * Time: 13:33
 */

namespace Ikarus\SPS\Plugin\Management\Observer;


use Ikarus\SPS\Plugin\Management\PluginManagementObserverInterface;

abstract class AbstractCallbackObserver
{
    /** @var callable */
    private $callback;

    /**
     * AbstractCallbackObserver constructor.
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

    protected function call(...$args) {
        call_user_func_array( $this->getCallback(), $args );
    }
}