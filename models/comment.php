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

	class Comment extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "comments");
		}
		
		public static function byGUID($guid)
		{
			//look up the token
			$sql = "
				SELECT id
				FROM comments
				WHERE guid = '".mysqli_real_escape_string($guid)."'";
			$id = db()->getValue($sql);
			
			//send it!
			return new Comment($id);
		}
    		
		public function getUrl()
		{
			return "/comment:{$this->id}";
		}
		
		public function getName()
		{
			return substr($this->get('comment_data'), 0, 32);
		}
	}
?>
