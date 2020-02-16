<?php
/**
 * BSD 3-Clause License
 *
 * Copyright (c) 2019, TASoft Applications
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 *  Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 *  Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace Ikarus\SPS\Plugin\Cyclic;


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;
use Ikarus\SPS\Storage\AtomicPersistentStorageInterface;
use Ikarus\SPS\Storage\PersistentStorageInterface;

class PersistentStoragePlugin extends AbstractCyclicPlugin
{
    const CMD_SAVE = 'ikarus.save';
    const CMD_RELOAD = 'ikarus.reload';

    protected $persistentPropertyDomain = [];
    /** @var PersistentStorageInterface */
    private $storage;
    private $loadAtFirstLoop, $_didLoad = false;

    /**
     * PersistentStoragePlugin constructor.
     * @param PersistentStorageInterface $storage
     * @param bool $loadAtFirstLoop
     * @param array $persistentPropertyKeys
     */
    public function __construct(PersistentStorageInterface $storage, bool $loadAtFirstLoop = true, array $persistentPropertyDomainsAndKeys = [])
    {
        $this->storage = $storage;
        $this->loadAtFirstLoop = $loadAtFirstLoop;

        foreach($persistentPropertyDomainsAndKeys as $domain => $keys) {
            $this->persistentPropertyDomain[$domain] = array_merge( $this->persistentPropertyDomain[$domain] ?? [], $keys );
        }
    }

    public function update(CyclicPluginManagementInterface $pluginManagement)
    {
        if((!$this->_didLoad && $this->loadAtFirstLoop) || $pluginManagement->hasCommand( static::CMD_RELOAD )) {
            $pluginManagement->clearCommand( static::CMD_RELOAD );

            $s = $this->getStorage();
            if($s instanceof AtomicPersistentStorageInterface)
                $s->openStorage();
            foreach ($this->persistentPropertyDomain as $domain => $keys) {
                foreach($keys as $key) {
                    $value = $s->loadValue($key, $domain);
                    $pluginManagement->putValue($value, $key, $domain);
                }
            }
        }

        if($pluginManagement->hasCommand( static::CMD_SAVE )) {
            $pluginManagement->clearCommand( static::CMD_SAVE );

            $s = $this->getStorage();

            foreach ($this->persistentPropertyDomain as $domain => $keys) {
                if(NULL === $keys) {
                    $values = $pluginManagement->fetchValue($domain);
                } else {
                    $values = [];
                    foreach($keys as $key) {
                        $values[$key] = $pluginManagement->fetchValue($domain, $key);
                    }
                }

                foreach($values as $key => $value)
                    $s->storeValue($value, $key, $domain);
            }

            if($s instanceof AtomicPersistentStorageInterface)
                $s->completeStorage();
        }
    }

    /**
     * @return PersistentStorageInterface
     */
    public function getStorage(): PersistentStorageInterface
    {
        return $this->storage;
    }
}