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

namespace Ikarus\SPS\Helper;


use Ikarus\SPS\Exception\RuntimeFatalErrorException;
use Throwable;

class ErrorManager
{
    const NOTICE_ERROR_LEVEL = 0;
    const DEPRECATED_ERROR_LEVEL = 1;
    const WARNING_ERROR_LEVEL = 2;
    const FATAL_ERROR_LEVEL = 3;


    /**
     * Classifies an internal PHP error code into a level like notices, warnings or errors
     *
     * @param int $internErrorCode
     * @return int
     */
    public static function detectErrorLevel(int $internErrorCode): int {
        switch ($internErrorCode) {
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_STRICT: return static::WARNING_ERROR_LEVEL;

            case E_NOTICE:
            case E_USER_NOTICE: return static::NOTICE_ERROR_LEVEL;

            case E_DEPRECATED:
            case E_USER_DEPRECATED: return static::DEPRECATED_ERROR_LEVEL;

            default:
                break;
        }

        return static::FATAL_ERROR_LEVEL;
    }

    public function prepareEnvironment() {
        set_error_handler(function($code, $msg, $file, $line) {
            $level = static::detectErrorLevel($code);

            if($level < self::FATAL_ERROR_LEVEL) {
                switch ($level) {
                    case static::NOTICE_ERROR_LEVEL    : echo CliColors::getColoredString( "** NOTICE    : ", CliColors::FG_DARK_GRAY); break;
                    case static::WARNING_ERROR_LEVEL   : echo CliColors::getColoredString( "** WARNING   : ", CliColors::FG_YELLOW); break;
                    case static::DEPRECATED_ERROR_LEVEL: echo CliColors::getColoredString( "** DEPRECATED: ", CliColors::FG_BROWN); break;
                    default:
                }
                echo $msg, " at $file on $line\n";
            } else {
                $e = new RuntimeFatalErrorException($msg, $code);
                $e->setFile($file);
                $e->setLine($line);
                throw $e;
            }
            return true;
        });
        set_exception_handler(function(Throwable $exception) {
            echo CliColors::getColoredString( "** ERROR     : ", CliColors::FG_RED);
            echo $exception->getMessage(), " at ", $exception->getFile(), " on ", $exception->getLine(), "\n";
        });
    }

    public function restoreEnvironment() {
        restore_error_handler();
        restore_exception_handler();
    }
}