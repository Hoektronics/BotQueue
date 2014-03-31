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
		$sql = "SELECT id
				FROM oauth_token
				WHERE token = ?";

		$id = db()->getValue($sql, array($key));

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
		$sql = "SELECT id, consumer_id
		    	FROM oauth_token
		    	WHERE ip_address = ?
		     	AND type = 1
		      	AND verified = 0
		      	AND (user_id = 0 || user_id = ?)
		    	ORDER BY id DESC";

		$requests = new Collection($sql, array($_SERVER['REMOTE_ADDR'], User::$me->id));
		$requests->bindType('id', 'OAuthToken');
		$requests->bindType('consumer_id', 'OAuthConsumer');

		return $requests;
	}

	public function getActiveBots()
	{
		$sql = "SELECT id, queue_id, job_id
				FROM bots
				WHERE oauth_token_id = ?
				AND status != 'retired'
				ORDER BY name";

		$bots = new Collection($sql, array($this->id));
		$bots->bindType('id', 'Bot');
		$bots->bindType('queue_id', 'Queue');
		$bots->bindType('job_id', 'Job');

		return $bots;
	}

	public function getName()
	{
		if ($this->get('name'))
			return $this->get('name');
		else
			return $this->getConsumer()->getName() . " #" . $this->id;
	}
}