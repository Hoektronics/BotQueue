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

	class ShortCode extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "shortcodes");
		}
		
		public static function byUrl($url)
		{
			$sql = "
				SELECT id
				FROM shortcodes
				WHERE url = '".mysqli_real_escape_string(db()->getLink(), $url)."'
			";
			
			$value = db()->getValue($sql);
			$code = new ShortCode($value);
			
			if (!$code->isHydrated())
			{
				$code->set('url', $url);
				$code->save();
			}
				
			return $code;
			
		}
		
		public static function byCode($code)
		{
			return new ShortCode(base_convert($code, 36, 10));
		}
		
		public function getCode()
		{
			return base_convert((int)$this->id, 10, 36);
		}
	}
?>
