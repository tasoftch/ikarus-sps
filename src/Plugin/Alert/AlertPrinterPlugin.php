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

namespace Ikarus\SPS\Plugin\Alert;


use Ikarus\SPS\Alert\AlertInterface;
use Ikarus\SPS\Alert\NoticeAlert;
use Ikarus\SPS\Alert\WarningAlert;
use Ikarus\SPS\Plugin\PluginInterface;

class AlertPrinterPlugin implements AlertPluginInterface
{
    public function handleAlert(AlertInterface $alert)
    {
        $date = (new \DateTime())->format ("Y-m-d G:i:s.u");

        if($alert instanceof NoticeAlert)
            $error = "[$date] \033[1;31mNotice ";
        elseif($alert instanceof WarningAlert)
            $error = "[$date] \033[0;35mWarning ";
        else
            $error = "[$date] \033[0;31mError ";

        $error .= sprintf("(%d)", $alert->getCode());
        if($pl = $alert->getAffectedPlugin())
            $error .= " <" . (( $pl instanceof PluginInterface ) ? $pl->getIdentifier() : (string)$pl) . "> ";
        $error .= $alert->getMessage() . "\033[0m" . PHP_EOL;
        echo $error;
    }

    public function recoverAlert($alert): bool
    {
        return false;
    }

    public function getIdentifier(): string
    {
        return "ikarus.logger.screen";
    }
}