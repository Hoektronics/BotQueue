<?php

class OAuthToken extends Model
{
	public static $REQUEST = "request";
	public static $VERIFIED = "verified";
	public static $ACCESS = "access";

	public function __construct($id = null)
	{
		parent::__construct($id, "oauth_token");

		$this->consumer = new OAuthConsumer($this->get('consumer_id'));
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
		$this->set('type', OAuthToken::$ACCESS);
		$this->set('verifier', '');
		$this->save();
	}

	public function isRequest()
	{
		return $this->get('type') == OAuthToken::$REQUEST;
	}

	public function isVerified()
	{
		return $this->get('type') == OAuthToken::$VERIFIED || $this->isAccess();
	}

	public function isAccess()
	{
		return $this->get('type') == OAuthToken::$ACCESS;
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
		     	AND type = ?
		      	AND (user_id IS NULL || user_id = ?)
		    	ORDER BY id DESC";

		$requests = new Collection($sql, array($_SERVER['REMOTE_ADDR'], OauthToken::$REQUEST, User::$me->id));
		$requests->bindType('id', 'OAuthToken');
		$requests->bindType('consumer_id', 'OAuthConsumer');

		return $requests;
	}

	public function getActiveBots()
	{
		$sql = "SELECT id
				FROM bots
				WHERE oauth_token_id = ?
				AND status != 'retired'
				ORDER BY name";

		$bots = new Collection($sql, array($this->id));
		$bots->bindType('id', 'Bot');

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