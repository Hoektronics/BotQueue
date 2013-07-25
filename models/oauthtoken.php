<?php
	class OAuthToken extends Model
	{	
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_token");
			
			$this->consumer = new OAuthConsumer($this->get('consumer_id'));
			$this->user = new User($this->get('user_id'));
		}

		public static function findByKey($key)
		{
			$id = db()->getValue("
				SELECT id
				FROM oauth_token
				WHERE token = '{$key}'"
			);

			return new OAuthToken($id);
		}
		
		public function getUrl()
		{
		  return "/app/token:{$this->id}";
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
		
		public function isMine()
		{
			return User::$me->id == $this->get('user_id');
		}
		
		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getConsumer()
		{
			return new OAuthConsumer($this->get('consumer_id'));
		}
		
		public static function getRequestTokensByIP()
		{
		  $sql = "
		    SELECT id, consumer_id
		    FROM oauth_token
		    WHERE ip_address = '" . db()->escape($_SERVER['REMOTE_ADDR']) . "'
		      AND type = 1
		      AND verified = 0
		      AND (user_id = 0 || user_id = '" . db()->escape(User::$me->id) .  "')
		    ORDER BY id DESC
		  ";
		  
		  return new Collection($sql, array('OAuthToken' => 'id', 'OAuthConsumer' => 'consumer_id'));
		}
		
		public function getBots()
		{
			$sql = "
				SELECT id, queue_id, job_id
				FROM bots
				WHERE oauth_token_id = ". db()->escape($this->id) ."
				ORDER BY name
			";

			return new Collection($sql, array('Bot' => 'id', 'Queue' => 'queue_id', 'Job' => 'job_id'));
		}
		
		public function getName()
		{
		  if ($this->get('name'))
		    return $this->get('name');
		  else
		    return $this->getConsumer()->getName() . " #" . $this->id;
		}
	}
?>