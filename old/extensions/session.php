<?
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

session_start();
// Create the CSRF token if it doesn't exist
if(!array_key_exists('CSRFToken', $_SESSION)) {
	$_SESSION['CSRFToken'] =  md5(uniqid(rand(), true));
}

User::authenticate();

//handle any login payloads.
if (User::isLoggedIn()) {
	if (!empty($_SESSION['payload'])) {
		$payload = $_SESSION['payload'];
		if ($payload['type'] == 'redirect') {
			header("Location: " . $payload['data']);
			unset($_SESSION['payload']);
			exit;
		}
	}
}