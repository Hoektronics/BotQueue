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

class BotTest extends BotQueue_Unit_Test
{
	/**
	 * @var $bot Bot
	 */
	protected $bot;

	protected $states;
	protected $setup;

	/**
	 * @param $from
	 * @param $to
	 * @param $isValid
	 */
	public function checkState($from, $to, $isValid)
	{
		$bot = new Bot();

		// Setup by transition to the correct state
		foreach ($this->setup[$from] AS $val) {
			$bot->setStatus($val);
		}

		$this->assertEquals($bot->getStatus(), $from);

		$exceptionThrown = false;

		try {
			$bot->setStatus($to);
		} catch (InvalidStateChange $ex) {
			$exceptionThrown = true;
		}

		if ($isValid)
			$this->assertEquals($bot->getStatus(), $to);
		else {
			$this->assertEquals($bot->getStatus(), $from);
			if(!$exceptionThrown)
				$this->fail($from . " to " . $to . " Failed");
		}
	}

	protected function setUp()
	{
		$this->bot = new Bot();
		$this->states = array();
		$this->setup = array();

		$this->setup[BotState::Idle] = array();
		$this->setup[BotState::Idle][] = BotState::Idle;
		$this->setup[BotState::Slicing][] = BotState::Idle;
		$this->setup[BotState::Slicing][] = BotState::Slicing;
		$this->setup[BotState::Working][] = BotState::Idle;
		$this->setup[BotState::Working][] = BotState::Working;
		$this->setup[BotState::Paused][] = BotState::Idle;
		$this->setup[BotState::Paused][] = BotState::Working;
		$this->setup[BotState::Paused][] = BotState::Paused;
		$this->setup[BotState::Waiting][] = BotState::Idle;
		$this->setup[BotState::Waiting][] = BotState::Working;
		$this->setup[BotState::Waiting][] = BotState::Waiting;
		$this->setup[BotState::Error][] = BotState::Idle;
		$this->setup[BotState::Error][] = BotState::Error;
		$this->setup[BotState::Maintenance][] = BotState::Maintenance;
		$this->setup[BotState::Offline][] = BotState::Offline;
		$this->setup[BotState::Retired][] = BotState::Retired;

		$this->states[BotState::Idle] = array();
		$this->states[BotState::Idle][BotState::Idle] = true;
		$this->states[BotState::Idle][BotState::Slicing] = true;
		$this->states[BotState::Idle][BotState::Working] = true;
		$this->states[BotState::Idle][BotState::Paused] = false;
		$this->states[BotState::Idle][BotState::Waiting] = false;
		$this->states[BotState::Idle][BotState::Error] = true;
		$this->states[BotState::Idle][BotState::Maintenance] = true;
		$this->states[BotState::Idle][BotState::Offline] = true;
		$this->states[BotState::Idle][BotState::Retired] = false;
		
		$this->states[BotState::Slicing] = array();
		$this->states[BotState::Slicing][BotState::Idle] = false;
		$this->states[BotState::Slicing][BotState::Slicing] = true;
		$this->states[BotState::Slicing][BotState::Working] = true;
		$this->states[BotState::Slicing][BotState::Paused] = true;
		$this->states[BotState::Slicing][BotState::Waiting] = true;
		$this->states[BotState::Slicing][BotState::Error] = true;
		$this->states[BotState::Slicing][BotState::Maintenance] = false;
		$this->states[BotState::Slicing][BotState::Offline] = true; //TODO: Fix this
		$this->states[BotState::Slicing][BotState::Retired] = false;
		
		$this->states[BotState::Working] = array();
		$this->states[BotState::Working][BotState::Idle] = true;
		$this->states[BotState::Working][BotState::Slicing] = true;
		$this->states[BotState::Working][BotState::Working] = true;
		$this->states[BotState::Working][BotState::Paused] = true;
		$this->states[BotState::Working][BotState::Waiting] = true;
		$this->states[BotState::Working][BotState::Error] = true;
		$this->states[BotState::Working][BotState::Maintenance] = true;
		$this->states[BotState::Working][BotState::Offline] = true;
		$this->states[BotState::Working][BotState::Retired] = false;

		$this->states[BotState::Paused] = array();
		$this->states[BotState::Paused][BotState::Idle] = false;
		$this->states[BotState::Paused][BotState::Slicing] = true;
		$this->states[BotState::Paused][BotState::Working] = true;
		$this->states[BotState::Paused][BotState::Paused] = true;
		$this->states[BotState::Paused][BotState::Waiting] = false;
		$this->states[BotState::Paused][BotState::Error] = false;
		$this->states[BotState::Paused][BotState::Maintenance] = true;
		$this->states[BotState::Paused][BotState::Offline] = true;
		$this->states[BotState::Paused][BotState::Retired] = false;

		$this->states[BotState::Waiting] = array();
		$this->states[BotState::Waiting][BotState::Idle] = true;
		$this->states[BotState::Waiting][BotState::Slicing] = false;
		$this->states[BotState::Waiting][BotState::Working] = true;
		$this->states[BotState::Waiting][BotState::Paused] = false;
		$this->states[BotState::Waiting][BotState::Waiting] = true;
		$this->states[BotState::Waiting][BotState::Error] = true;
		$this->states[BotState::Waiting][BotState::Maintenance] = false;
		$this->states[BotState::Waiting][BotState::Offline] = true; //todo fix this
		$this->states[BotState::Waiting][BotState::Retired] = false;

		$this->states[BotState::Error] = array();
		$this->states[BotState::Error][BotState::Idle] = true;
		$this->states[BotState::Error][BotState::Slicing] = false;
		$this->states[BotState::Error][BotState::Working] = false;
		$this->states[BotState::Error][BotState::Paused] = false;
		$this->states[BotState::Error][BotState::Waiting] = false;
		$this->states[BotState::Error][BotState::Error] = true;
		$this->states[BotState::Error][BotState::Maintenance] = true;
		$this->states[BotState::Error][BotState::Offline] = false;
		$this->states[BotState::Error][BotState::Retired] = false;

		$this->states[BotState::Offline] = array();
		$this->states[BotState::Offline][BotState::Idle] = true;
		$this->states[BotState::Offline][BotState::Slicing] = false;
		$this->states[BotState::Offline][BotState::Working] = false;
		$this->states[BotState::Offline][BotState::Paused] = false;
		$this->states[BotState::Offline][BotState::Waiting] = false;
		$this->states[BotState::Offline][BotState::Error] = false;
		$this->states[BotState::Offline][BotState::Maintenance] = true;
		$this->states[BotState::Offline][BotState::Offline] = true;
		$this->states[BotState::Offline][BotState::Retired] = true;

		$this->states[BotState::Retired] = array();
		$this->states[BotState::Retired][BotState::Idle] = false;
		$this->states[BotState::Retired][BotState::Slicing] = false;
		$this->states[BotState::Retired][BotState::Working] = false;
		$this->states[BotState::Retired][BotState::Paused] = false;
		$this->states[BotState::Retired][BotState::Waiting] = false;
		$this->states[BotState::Retired][BotState::Error] = false;
		$this->states[BotState::Retired][BotState::Maintenance] = false;
		$this->states[BotState::Retired][BotState::Offline] = false;
		$this->states[BotState::Retired][BotState::Retired] = true;
	}

	public function testDefaultOffline()
	{
		$this->assertEquals($this->bot->getStatus(), BotState::Offline);
	}

	public function testAllStates()
	{
		$this->assertFalse(empty($this->setup));
		$this->assertFalse(empty($this->states));
		foreach ($this->states AS $from => $next) {
			foreach ($next AS $to => $isValid) {
				$this->checkState($from, $to, $isValid);
			}
		}
	}
}

?>