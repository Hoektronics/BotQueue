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
		
		public static function byContentAndType($id, $type)
		{
      //some validation
		  $id = (int)$id;
		  if (!in_array($type, array('job', 'bot')))
		    return new Comment();
		  
			//look up the comments
			$sql = "
				SELECT id, user_id
				FROM comments
				WHERE content_id = {$id}
				  AND content_type = '" . $type . "'
        ORDER BY comment_date ASC
			";
			return new Collection($sql, array('Comment' => 'id', 'User' => 'user_id'));
		}
		
		public static function getContent($id, $type)
		{
		  if ($type == 'bot')
		    return new Bot($id);
		  elseif ($type == 'job')
		    return new Job($id);
		  else
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
		
		public function getName()
		{
			return substr($this->get('comment_data'), 0, 32);
		}
	}
?>