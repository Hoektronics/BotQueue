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

class Comment extends Model
{
	// todo: modify the database so we can refactor this out.
	// Types as key => Class name
	private static $types = array(
		'job' => 'Job',
		'bot' => 'Bot'
	);

	public function __construct($id = null)
	{
		parent::__construct($id, "comments");
	}

	public static function byContentAndType($id, $type)
	{
		if (!array_key_exists($type, self::$types)) {
			$comments = Collection::none();
		} else {
			//look up the comments
			$sql = "SELECT id, user_id
				FROM comments
				WHERE content_id = ?
				AND content_type = ?
				ORDER BY comment_date ASC
			";

			$comments = new Collection($sql, array($id, $type));
		}
		$comments->bindType('id', 'Comment');
		$comments->bindType('user_id', 'User');

		return $comments;
	}

	/**
	 * @param $id
	 * @param $type
	 * @return Bot|Job|null
	 */
	public static function getContent($id, $type)
	{
		if (array_key_exists($type, self::$types)) {
			return new self::$types[$type]($id);
		}
		return null;
	}

	public function getUser()
	{
		return new User($this->get('user_id'));
	}

	public function getUrl()
	{
		return "/comment:{$this->id}";
	}

	// todo: Figure out what this function was designed for since comment_data doesn't exist
	public function getName()
	{
		return substr($this->get('comment_data'), 0, 32);
	}
}