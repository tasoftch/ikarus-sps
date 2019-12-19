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

namespace Ikarus\SPS\Event;

use Ikarus\SPS\Plugin\Error\ErrorInterface;

/**
 * Listen for this event to receive error notifications from plugins (if available)
 *
 * @package Ikarus\SPS\Event
 */
class PluginErrorEvent extends ResponseEvent
{
    private $code;
    private $file;
    private $line;
    private $className;

    public function __construct($code, $message, $file, $line, $class)
    {
        parent::__construct($message ?: "");
        $this->code = $code;
        $this->file = $file;
        $this->line = $line;
        $this->className = $class;
    }

    public function serialize()
    {
        return serialize([
            $this->code,
            $this->file,
            $this->line,
            $this->className,
            parent::serialize()
        ]);
    }

    public function unserialize($serialized)
    {
        list($this->code, $this->file, $this->line, $this->className, $parent) = unserialize($serialized);
        parent::unserialize($parent);
    }

    /**
     * @return ErrorInterface
     */
    public function getError(): ErrorInterface {
        $c = $this->className;
        return new $c($this->code, $this->getResponse(), $this->file, $this->line);
    }

    /**
     * At least one listener should stop propagation to inform the plugin's event handler, that the event handling is in process.
     */
    public function stopPropagation()
    {
        parent::stopPropagation();
    }
}