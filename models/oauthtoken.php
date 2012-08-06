<?php
	class OAuthToken extends Model
	{	
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_token");
			
			$this->consumer = new OAuthConsumer($this->get('consumer_id'));
			$this->user = new User($this->get('user_id'));
		}

		/* static functions */
		
		public static function findByKey($key)
		{
			$id = db()->getValue("
				SELECT id
				FROM oauth_token
				WHERE token = '{$key}'"
			);

			return new OAuthToken($id);
		}
		
		public function changeToAccessToken()
		{
			$this->set('token', MyOAuthProvider::generateToken());
			$this->set('token_secret', MyOAuthProvider::generateToken());
			$this->set('type', 2);
			$this->set('verifier', '');
			$this->save();
		}
		
		public function isRequest()
		{
			return $this->get('type') == 1;
		}
		
		public function isAccess()
		{
			return !$this->isRequest();
		}
		
		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getConsumer()
		{
			return new OAuthConsumer($this->get('consumer_id'));
		}
	}
?>