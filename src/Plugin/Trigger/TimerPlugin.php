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

namespace Ikarus\SPS\Plugin\Trigger;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\PluginManagementInterface;

class TimerPlugin extends AbstractPlugin implements TriggerPluginInterface
{
    private $interval;
    private $times;

    /**
     * TimerPlugin constructor.
     * @param float $interval
     * @param int $times
     * @param string $eventName
     */
    public function __construct($interval, $times = 0, $eventName = "")
    {
        parent::__construct($eventName);
        $this->interval = $interval;
        $this->times = $times;
    }


    public function run(PluginManagementInterface $manager)
    {
        $count = 0;
        while (1) {
            $count++;

            if($this->getTimes() > 0 AND $this->getTimes() < $count)
                break;

            usleep( $this->getInterval() * 1000000 );
            $manager->dispatchEvent($this->getEventName());
        }
    }

    /**
     * @return float
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param float $interval
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    /**
     * @return int
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @param int $times
     */
    public function setTimes($times)
    {
        $this->times = $times;
    }

}