<?php
/*
	This file is part of BotQueue.

	BotQueue is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	BotQueue is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

class Config_Define_Converter_Test extends BotQueue_Unit_Test {

	public function testConvertDefineString() {
		/** @var string $key */
		$key = "COMPANY_NAME";

		/** @var string $value */
		$value = "BotQueue";

		/** @var array $config */
		$config = ConfigConverter::convertDefines($this->getDefine($key, $value));

		$this->assertArrayHasKey($key, $config);
		$this->assertEquals($value, $config[$key]);
	}

	public function testConvertInvalidKey() {
		$key = "2COOL4TESTS";

		$defineToConvert = $this->getDefine($key, false);

		$this->setExpectedException("InvalidConfigDefine");

		ConfigConverter::convertDefines($defineToConvert);
	}

	public function testConvertBooleanValue() {
		$key = "TESTS_ARE_COOL";
		$value = false;

		$config = ConfigConverter::convertDefines($this->getDefine($key, $value));

		$this->assertArrayHasKey($key, $config);
		$this->assertEquals($value, $config[$key]);
	}

	public function testConvertWithLineBreak() {
		$key = "IM_A_LITTLE_TEAPOT";
		$value = "SHORT_AND_STOUT";

		$defineToConvert = "\ndefine\n(\n\"".$key."\",\n\"".$value."\"\n);";

		$config = ConfigConverter::convertDefines($defineToConvert);

		$this->assertArrayHasKey($key, $config);
		$this->assertEquals($value, $config[$key]);
	}

	public function testMultipleDefines() {
		$keys = array('a' => 1, 'b' => "2", 'c' => false);

		$definesToConvert = "";
		foreach($keys as $key => $value) {
			$definesToConvert .= $this->getDefine($key, $value);
		}

		$config = ConfigConverter::convertDefines($definesToConvert);

		foreach($keys as $key => $value) {
			$this->assertArrayHasKey($key, $config);
			$this->assertEquals($value, $config[$key]);
		}
	}

	public function getDefine($key, $value) {
		if ($value === true) {
			return "define(\"".$key."\", true);";
		} else if($value === false) {
			return "define(\"".$key."\", false);";
		} else if(is_int($value)) {
			return "define(\"".$key."\", ".$value.");";
		}
		return "define(\"".$key."\", \"".$value."\");";
	}

}
 