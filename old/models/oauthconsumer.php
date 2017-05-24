<?php

class OAuthConsumer extends Model
{

	public function __construct($id = null)
	{
		parent::__construct($id, "oauth_consumer");
	}

	public static function create($name, $app_url)
	{
		$app = new OAuthConsumer();
		$app->set('name', $name);
		$app->set('app_url', $app_url);
		$app->set('user_id', User::$me->id);
		$app->set('consumer_key', MyOAuthProvider::generateToken());
		$app->set('consumer_secret', MyOAuthProvider::generateToken());
		$app->set('active', 1);
		$app->save();
		return $app;
	}

	public function canEdit()
	{
		if (User::$me->isAdmin())
			return true;

		if (User::isLoggedIn() && $this->get('user_id') == User::$me->id)
			return true;

		return false;
	}

	public static function findByKey($key)
	{
		$sql = "SELECT id
				FROM oauth_consumer
				WHERE consumer_key = ?";

		$id = db()->getValue($sql, array($key));

		return new OAuthConsumer($id);
	}

	public function getUrl()
	{
		return '/app:' . $this->id;
	}

	public function getName()
	{
		return $this->get('name');
	}

	public function isActive()
	{
		return $this->get('active');
	}

	public function getKey()
	{
		return $this->get('consumer_key');
	}

	public function getSecretKey()
	{
		return $this->get('consumer_secret');
	}

	public function hasNonce($nonce, $timestamp)
	{
		/*
		  $timestamp = (int)$timestamp;
		  $nonce = (int)$nonce;
		  
			$check = db()->getValue("
				SELECT count(*) AS cnt 
				FROM oauth_consumer_nonce
				WHERE timestamp = {$timestamp}
					AND nonce = {$nonce}
					AND consumer_id = {$this->id}
			");

			return ($check==1);
			*/

		return true;
	}

	public function addNonce($nonce)
	{
		/*
			$n = new OAuthConsumerNonce();
			$n->set('consumer_id', $this->id);
			$n->set('timestamp', time());
			$n->set('nonce', $nonce);
			$n->save();
			
			return $n;
			*/
	}

	public function delete()
	{
		//delete all our tokens
		db()->execute("DELETE FROM oauth_token WHERE consumer_id = ?", array($this->id));

		//delete all our nonces
		db()->execute("DELETE FROM oauth_consumer_nonce WHERE consumer_id = ?", array($this->id));

		parent::delete();
	}

	public function getApps()
	{
		$sql = "SELECT id
				FROM oauth_token
				WHERE consumer_id = ?
				AND user_id = ?";

		$apps = new Collection($sql, array($this->id, User::$me->id));
		$apps->bindType('id', 'OAuthToken');

		return $apps;
	}
}