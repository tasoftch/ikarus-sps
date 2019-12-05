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


class CliColors {
    const FG_BLACK = '0;30';
    const FG_DARK_GRAY = '1;30';
    const FG_BLUE = '0;34';
    const FG_LIGHT_BLUE = '1;34';
    const FG_GREEN = '0;32';
    const FG_LIGHT_GREEN = '1;32';
    const FG_CYAN = '0;36';
    const FG_LIGHT_CYAN = '1;36';
    const FG_RED ='0;31';
    const FG_LIGHT_RED ='1;31';
    const FG_PURPLE ='0;35';
    const FG_LIGHT_PURPLE ='1;35';
    const FG_BROWN ='0;33';
    const FG_YELLOW ='1;33';
    const FG_LIGHT_GRAY ='0;37';
    const FG_WHITE ='1;37';

    const BG_BLACK ='40';
    const BG_RED ='41';
    const BG_GREEN ='42';
    const BG_YELLOW ='43';
    const BG_BLUE = '44';
    const BG_MAGENTA ='45';
    const BG_CYAN ='46';
    const BG_LIGHT_GRAY ='47';


    // Returns colored string
    public static function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if ($foreground_color) {
            $colored_string .= "\033[{$foreground_color}m";
        }
        // Check if given background color found
        if ($background_color) {
            $colored_string .= "\033[{$background_color}m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }
}