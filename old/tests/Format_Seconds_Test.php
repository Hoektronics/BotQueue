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

class Format_Seconds_Test extends BotQueue_Unit_Test {

	const minutes_to_seconds = 60;
	const hours_to_seconds = 3600;
	const days_to_seconds = 86400;

	protected function setUp() {

	}

	public function testTime()
	{
		$this->testSeconds();
		$this->testMinutes();
		$this->testHours();
		$this->testDays();
		$this->testMonths();
		//$this->testYears();
	}

	/**
	 * @param int $start
	 * @param int $end
	 * @param string $test
	 * @param int $increment
	 */
	public function evaluateRange($start, $end, $test, $increment = 1) {
		for($seconds = $start; $seconds <= $end; $seconds += $increment) {
			$this->assertEquals($test,
				Utility::formatSeconds($seconds));
		}
	}

	public function testSeconds()
	{
		$this->evaluateRange(1, 4, 'less than 5 seconds');
		$this->evaluateRange(5, 9, 'less than 10 seconds');
		$this->evaluateRange(10, 19, 'less than 20 seconds');
		$this->evaluateRange(20, 40, 'half a minute');
		$this->evaluateRange(41, 59, 'less than a minute');
	}

	public function testMinutes()
	{
		$this->evaluateRange(60, 119, '1 minute');

		for ($minute = 2; $minute <= 59; $minute++) {
			$this->evaluateRange(
				self::minutes_to_seconds * $minute,
				self::minutes_to_seconds * $minute + 59,
				$minute . " minutes",
				self::minutes_to_seconds
			);
		}
	}

	public function testHours()
	{
		$this->evaluateRange(
			self::hours_to_seconds,
			self::hours_to_seconds + 59 * self::minutes_to_seconds,
			'1 hour',
			self::minutes_to_seconds
		);

		for ($hour = 2; $hour <= 23; $hour++) {
			// The first half hour is about x hours,
			// The second half hour is about x+1 hours.
			// So 2 hours 29 minutes, 59 seconds is about 2 hours,
			// but 2 hours 30 minutes is about 3 hours
			$this->evaluateRange(
				self::hours_to_seconds * $hour,
				self::hours_to_seconds * $hour + 29 * self::minutes_to_seconds + 59,
				"about " . $hour . " hours",
				self::minutes_to_seconds
			);

			$this->evaluateRange(
				self::hours_to_seconds * $hour + 30 * self::minutes_to_seconds,
				self::hours_to_seconds * $hour + 59 * self::minutes_to_seconds + 59,
				"about " . ($hour + 1) . " hours",
				self::minutes_to_seconds
			);
		}
	}

	public function testDays() {
		$this->evaluateRange(
			self::days_to_seconds,
			self::days_to_seconds + 23 * self::hours_to_seconds,
			"1 day",
			self::hours_to_seconds
		);

		for($day = 2; $day <= 27; $day++) {
			$this->evaluateRange(
				self::days_to_seconds * $day,
				self::days_to_seconds * $day + 11 * self::hours_to_seconds,
				$day . " days",
				self::hours_to_seconds
			);

			$this->evaluateRange(
				self::days_to_seconds * $day + 12 * self::hours_to_seconds,
				self::days_to_seconds * $day + 23 * self::hours_to_seconds,
				($day + 1) . " days",
				self::hours_to_seconds
			);
		}
	}

	public function testMonths() {
		// TODO: Find out where the month boundary should be
	}

}
 