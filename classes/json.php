<?
	class JSON
	{
		private function __construct()
		{
		}
		
		public static function decode($data)
		{
			return json_decode($data);
		}
		
		public static function encode($data)
		{
			return json_encode($data);
		}
	}
?>