<?
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
				WHERE url = '{$url}'
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
