<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 07/03/20
 * Time: 13:32
 */

namespace Ikarus\SPS\Plugin\Management\Observer;


use Ikarus\SPS\Plugin\Management\PluginManagementObserverInterface;

class DomainObserver extends AbstractCallbackObserver implements PluginManagementObserverInterface
{
    /** @var string */
    private $domain;

    public function __construct(callable $callback, string $domain)
    {
        parent::__construct($callback);
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    public function trigger(array $changes)
    {
        $this->call($changes[self::DOMAIN_KEY], $changes[self::KEY_KEY], $changes[self::VALUE_KEY]);
    }

    public function shouldTrigger(array $changes): bool
    {
        return (isset($changes[ self::DOMAIN_KEY ]) && $changes[self::DOMAIN_KEY] == $this->getDomain());
    }
}