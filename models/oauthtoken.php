<?php
	class OAuthToken extends Model
	{	
		//todo: nuke this.
		protected $type;
		protected $consumer;
		protected $token;
		protected $token_secret;
		protected $callback;
		protected $verifier;
		protected $user;
		protected $pdo;
		
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_consumer");
			
			//nuke this.
			$this->load();
		}

		/* static functions */
		
		public static function createRequestToken($consumer, $token, $tokensecret, $callback)
		{
			$t = new OAuthToken();
			$t->set('type', 1);
			$t->set('consumer_id', $consumer->id);
			$t->set('token', $token);
			$t->set('token_secret', $tokensecret);
			$t->set('callback_url', $callback);
			$t->save();
			
			return $t;
		}
		
		public static function findByKey($key)
		{
			$id = db()->query("
				SELECT id
				FROM oauth_token
				WHERE token = '{$key}'"
			);

			return new OAuthToken($id);
		}		
		
		//todo: add getConsumer() call.
		private function load(){
			$this->consumer = new OAuthConsumer($this->get('consumer_id'));
			$this->user = new User($this->get('user_id'));
		}
		
		public function changeToAccessToken($token,$secret)
		{
			if($this->isRequest())
			{
				$this->set('token', $token);
				$this->set('token_secret', $secret);
				$this->set('type', 2);
				$this->set('verifier', '');
				$this->set('callback_url', '');
				$this->save();
				
				return true;
			}
			else
				return false;
		}
		
		/* some setters */
		
		public function setVerifier($verifier)
		{
			$this->set('verifier', $verifier);
			$this->save();
			
			$this->verifier = $verifier;
		}
		
		public function setUser($user)
		{
			$this->set('user_id', $user->id);
			$this->save();
			
			$this->user = $user;
		}
		
		/* some getters */
		
		public function isRequest(){
			return $this->type == 1;
		}
		
		public function isAccess(){
			return !$this->isRequest();
		}
		
		//todo: nuke me
		public function getCallback(){
			return $this->callback;
		}
		
		//todo: nuke me
		public function getVerifier(){
			return $this->verifier;
		}
		
		//todo: nuke me
		public function getType(){
			return $this->type;
		}
		
		//todo: nuke me
		public function getSecret(){
			return $this->token_secret;
		}
		
		//todo: nuke me
		public function getUser(){
			return $this->user;
		}	
	}
?>