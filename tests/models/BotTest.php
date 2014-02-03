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


//include("extensions/global.php");
//require(MODELS_DIR.'Bot/BotState.php');

class BotTest extends BotQueue_Unit_Test {
	/**
	 * @var $bot Bot
	 */
	protected $bot;

	protected function setUp()
	{
		$this->bot = new Bot();
	}

	public function testDefaultOffline() {
		$this->assertEquals($this->bot->getStatus(), BotState::Offline);
	}

	public function testOfflineToIdle() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
	}

	public function testIdleToIdle() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
	}

	public function testIdleToSlicing() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Slicing);
		$this->assertEquals($this->bot->getStatus(), BotState::Slicing);
	}

	public function testIdleToWorking() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Working);
		$this->assertEquals($this->bot->getStatus(), BotState::Working);
	}

	public function testIdleToPaused() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->setExpectedException("InvalidStateChange");
		$this->bot->setStatus(BotState::Paused);
	}

	public function testIdleToWaiting() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->setExpectedException("InvalidStateChange");
		$this->bot->setStatus(BotState::Waiting);
	}

	public function testIdleToError() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Error);
		$this->assertEquals($this->bot->getStatus(), BotState::Error);
	}

	public function testIdleToMaintenance() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Maintenance);
		$this->assertEquals($this->bot->getStatus(), BotState::Maintenance);
	}

	public function testIdleToOffline() {
		$this->bot->setStatus(BotState::Idle);
		$this->assertEquals($this->bot->getStatus(), BotState::Idle);
		$this->bot->setStatus(BotState::Offline);
		$this->assertEquals($this->bot->getStatus(), BotState::Offline);
	}

}
?>