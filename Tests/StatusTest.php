<?php
/*
 * BSD 3-Clause License
 *
 * Copyright (c) 2023, TASoft Applications
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

use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface as MR;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
	public function testStatus() {

		$table = [										// FUNCS		ON		OFF		ERR		MAN		MAN_ON
			[0,0,0,0,0],			# 00 REGISTER						0		0		0		0		0
			[0,1,0,0,0],			# 01 OFF							0		1		0		0		0
			[1,0,0,0,0],			# 02 ON								1		0		0		0		0
			[1,0,0,0,0],			# 03 OFF ON							1		0		0		0		0
			[0,1,1,0,0],			# 04 ERR							0		1		1		0		0
			[0,1,1,0,0],			# 05 OFF ERR						0		1		1		0		0
			[0,1,1,0,0],			# 06 ON ERR							0		1		1		0		0
			[0,1,1,0,0],			# 07 OFF ON ERR						0		1		1		0		0

			[0,1,0,1,0],			# 08 MANUAL							0		1		0		1		0
			[0,1,0,1,0],			# 09 MANUAL OFF						0		1		0		1		0
			[0,1,0,1,0],			# 10 MANUAL ON						0		1		0		1		0
			[0,1,0,1,0],			# 11 MANUAL OFF ON					0		1		0		1		0
			[0,1,1,1,0],			# 12 MANUAL ERR						0		1		1		1		0
			[0,1,1,1,0],			# 13 MANUAL OFF ERR					0		1		1		1		0
			[0,1,1,1,0],			# 14 MANUAL ON ERR					0		1		1		1		0
			[0,1,1,1,0],			# 15 MANUAL OFF ON ERR				0		1		1		1		0

			[1,0,0,1,0],			# 16 MANUAL_ON REGISTER				1		0		0		1		0
			[1,0,0,1,0],			# 17 MANUAL_ON OFF					1		0		0		1		0
			[1,0,0,1,0],			# 18 MANUAL_ON ON					1		0		0		1		0
			[1,0,0,1,0],			# 19 MANUAL_ON OFF ON				1		0		0		1		0
			[0,1,1,1,0],			# 20 MANUAL_ON ERR					0		1		1		1		0
			[0,1,1,1,0],			# 21 MANUAL_ON OFF ERR				0		1		1		1		0
			[0,1,1,1,0],			# 22 MANUAL_ON ON ERR				0		1		1		1		0
			[0,1,1,1,0],			# 23 MANUAL_ON OFF ON ERR			0		1		1		1		0

			[1,0,0,1,0],			# 24 MANUAL_ON MANUAL				1		0		0		1		0
			[1,0,0,1,0],			# 25 MANUAL_ON MANUAL OFF			1		0		0		1		0
			[1,0,0,1,0],			# 26 MANUAL_ON MANUAL ON			1		0		0		1		0
			[1,0,0,1,0],			# 27 MANUAL_ON MANUAL OFF ON		1		0		0		1		0
			[0,1,1,1,0],			# 28 MANUAL_ON MANUAL ERR			0		1		1		1		0
			[0,1,1,1,0],			# 29 MANUAL_ON MANUAL OFF ERR		0		1		1		1		0
			[0,1,1,1,0],			# 30 MANUAL_ON MANUAL ON ERR		0		1		1		1		0
			[0,1,1,1,0],			# 31 MANUAL_ON MANUAL OFF ON ERR	0		1		1		1		0
		];

		$hdl = [
			function($s) { return AbstractPlugin::isStatusOn($s); },
			function($s) { return AbstractPlugin::isStatusOff($s); },
			function($s) { return AbstractPlugin::isStatusError($s); },
			function($s) { return AbstractPlugin::isStatusManual($s); },
			function($s) { return AbstractPlugin::isStatusPanel($s); }
		];

		foreach($table as $idx => $value) {
			foreach($hdl as $ii => $closure) {
				if($value[$ii])
					$this->assertTrue( $closure($idx), "Fehler für Status $idx in $ii" );
				else
					$this->assertFalse( $closure($idx), "Fehler für Status $idx in $ii" );
			}
		}
	}

	public function testStatusManipulation() {
		$status = AbstractPlugin::statusEnable( MR::STATUS_MANUAL | MR::STATUS_PANEL );

		$this->assertTrue( (bool) ($status & MR::STATUS_ON) );
		$this->assertFalse(AbstractPlugin::isStatusOn($status));
		$this->assertTrue(AbstractPlugin::isStatusManual($status));

		$status = AbstractPlugin::statusManualRelease($status);

		$this->assertTrue( (bool) ($status & MR::STATUS_ON) );
		$this->assertTrue(AbstractPlugin::isStatusOn($status));
		$this->assertFalse(AbstractPlugin::isStatusManual($status));

		$status = AbstractPlugin::statusError($status);

		$this->assertTrue( (bool) ($status & MR::STATUS_ON) );
		$this->assertFalse(AbstractPlugin::isStatusOn($status));
		$this->assertFalse(AbstractPlugin::isStatusManual($status));
		$this->assertTrue(AbstractPlugin::isStatusError($status));
	}
}
