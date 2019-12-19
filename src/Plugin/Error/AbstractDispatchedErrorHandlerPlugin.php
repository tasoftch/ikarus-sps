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

namespace Ikarus\SPS\Plugin\Error;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\DispatchedErrorHandlerPluginInterface;
use Ikarus\SPS\Plugin\PluginManagementInterface;
use Throwable;

abstract class AbstractDispatchedErrorHandlerPlugin extends AbstractPlugin implements DispatchedErrorHandlerPluginInterface
{
    private $error_reporting = E_ALL;

    /**
     * AbstractDispatchedErrorHandlerPlugin constructor.
     * @param int $error_reporting
     */
    public function __construct($error_reporting = E_ALL)
    {
        $this->error_reporting = $error_reporting;
    }


    /**
     * @return int
     */
    public function getErrorReporting(): int
    {
        return $this->error_reporting;
    }

    /**
     * Handles the error. If done, return true otherwise Ikarus will continue handling the error.
     *
     * @param ErrorInterface $error
     * @param PluginManagementInterface $management
     * @return bool
     */
    protected function handleError(ErrorInterface $error, PluginManagementInterface $management): bool {
        return $error instanceof Fatal ? false : true;
    }

    /**
     * @inheritDoc
     */
    public function setupErrorEnvironment(PluginManagementInterface $management)
    {
        set_error_handler(function($code, $msg, $file, $line) use ($management) {
            $bool = false;

            $c = (function() use ($code) {
                switch ($code) {
                    case E_WARNING:
                    case E_USER_WARNING:
                    case E_CORE_WARNING:
                    case E_COMPILE_WARNING:
                    case E_STRICT: return Warning::class;

                    case E_NOTICE:
                    case E_USER_NOTICE: return Notice::class;

                    case E_DEPRECATED:
                    case E_USER_DEPRECATED: return Deprecated::class;

                    default:
                        break;
                }

                return Fatal::class;
            })();

            if($this->getErrorReporting() & $code) {
                $bool = $this->handleError( new $c($code, $msg, $file, $line), $management );
            }

            if(!$bool) {
                if($c == Fatal::class) {
                    $management->stopEngine($code, $msg);
                    usleep(10000);
                    exit();
                }
            }
            return true;
        });

        set_exception_handler(function(Throwable $throwable) use ($management) {
            return $this->handleError( new Exception($throwable->getCode(), $throwable->getMessage(), $throwable->getFile(), $throwable->getLine()), $management );
        });
    }
}