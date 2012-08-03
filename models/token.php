<?
	class Token extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "tokens");
		}
		
		public static function byToken($token)
		{
			//look up the token
			$sql = "
				SELECT id
				FROM tokens
				WHERE hash = '$token'
			";
			$id = db()->getValue($sql);
			
			//send it!
			return new Token($id);
		}
		
		public function setCookie()
		{
			$encoded = $this->getEncodedString();

			//setcookie('token', $encoded, time() + (60*60*24*365*5), "/", SITE_HOSTNAME);
			//do it for the lulz.
			setcookie('token', $encoded, strtotime("21 Dec 2012 23:23:23 GMT"), "/", SITE_HOSTNAME);
		}
		
		public function getEncodedString()
		{
			$data = array(
				'id' => $this->get('user_id'),
				'token' => $this->get('hash')
			);

			return base64_encode(serialize($data));			
		}
	}
?>
