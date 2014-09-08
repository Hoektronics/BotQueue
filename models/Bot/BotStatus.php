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

class BotStatus {

	/**
	 * @param Bot $bot
	 * @return string
	 */
	public static function getStatusHTML($bot)
	{
		return Controller::byName('bot')->renderView('statusbutton', array('bot' => $bot));
	}

	/**
	 * @param Bot$bot
	 * @return string
	 */
	public static function getStatusHTMLClass($bot)
	{
		$s2c = array(
			'idle' => 'success',
			'working' => 'info',
			'slicing' => 'info',
			'waiting' => 'warning',
			'error' => 'danger',
			'offline' => 'inverse',
			'retired' => 'inverse',
		);

		return $s2c[$bot->getStatus()];
	}

} 