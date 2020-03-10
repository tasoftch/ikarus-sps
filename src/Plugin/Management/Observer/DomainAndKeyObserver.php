<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 07/03/20
 * Time: 14:10
 */

namespace Ikarus\SPS\Plugin\Management\Observer;


class DomainAndKeyObserver extends DomainObserver
{
    private $keys;

    public function __construct(callable $callback, string $domain, array $keys)
    {
        parent::__construct($callback, $domain);
        $this->keys = $keys;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    public function shouldTrigger(array $changes): bool
    {
        return parent::shouldTrigger($changes) && isset($changes[self::KEY_KEY]) && in_array($changes[self::KEY_KEY], $this->getKeys());
    }
}