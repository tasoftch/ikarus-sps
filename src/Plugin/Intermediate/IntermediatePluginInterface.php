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

namespace Ikarus\SPS\Plugin\Intermediate;


use Ikarus\SPS\Plugin\PluginInterface;

/**
 * The intermediate plugins are designed to establish a connection between two SPS engines or an sps engine and another application.
 * The plugins implementing this interface setup a socket and listen for incoming connections.
 * If someone connects, then the plugin does the following:
 *  1. Accepts the connection (one by one!)
 *  2. Reads the request
 *  3. Transform the request into an SPS conform command
 *  4. Waits for the SPS to respond
 *  5. Sends the response back to the requesting client
 *  6. Closes the connection and wait for the next client.
 *
 * @package Ikarus\SPS\Plugin\Intermediate
 */
interface IntermediatePluginInterface extends PluginInterface
{
    /**
     * Gets a communication address like an IP address or a unix socket address
     * After successful connection, this method must return the current used name
     *
     * @return string
     */
    public function getAddress(): string;

    /**
     * Gets a port number to establish a connection (or null in case of unix connections)
     * After successful connection, this method must return the current used port number
     *
     * @return int|null
     */
    public function getPort(): ?int;
}