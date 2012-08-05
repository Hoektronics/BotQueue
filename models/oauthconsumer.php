<?php
	class OAuthConsumer extends Model
	{
		
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_consumer");

			if($id != 0)
				$this->load();
		}

		public static function findByKey($key)
		{
			$id = db()->query("
				SELECT id
				FROM oauth_consumer
				WHERE consumer_key = '{$key}'"
			);

			return new OAuthConsumer($id);
		}
		
		//todo: nuke this.
		private function load()
		{
			$this->key = $this->get('consumer_key');
			$this->secret = $this->get('consumer_secret');
			$this->active = $this->get('active');
		}
		
		public static function create($key, $secret)
		{
			$c = new OAuthConsumer();
			$c->set('consumer_key', $key);
			$c->set('consumer_secret', $secret);
			$c->set('active', 1);
			$c->save();
			
			return $c;
		}
		
		public function isActive()
		{
			return $this->get('active');
		}
		
		public function getKey(){
			return $this->get('consumer_key');
		}
		
		public function getSecretKey(){
			return $this->get('consumer_secret');
		}
		
		public function hasNonce($nonce, $timestamp)
		{
			$check = db()->getValue("
				SELECT count(*) AS cnt 
				FROM oauth_consumer_nonce
				WHERE timestamp = '{$timestamp}'
					AND nonce = '{$nonce}'
					AND consumer_id = {$this->id}
			");

			return ($check['cnt']==1);
		}
		
		//todo: create OAuthConsumerNonce
		//todo: make sure calls to this are okay.
		public function addNonce($nonce)
		{
			$n = new OAuthConsumerNonce();
			$n->set('consumer_id', $this->id);
			$n->set('timestamp', time());
			$n->set('nonce', $nonce);
			$n->save();
			
			return $n;
		}
		
		/* setters */
		
		//todo: nuke this.
		public function setKey($key){
			$this->key = $key;
		}
		
		//todo: nuke this.
		public function setSecret($secret){
			$this->secret = $secret;
		}
		
		//todo: nuk this.
		public function setActive($active){
			$this->active = $active;
		}
		
		//todo: nuke this.
		public function setId($id){
			$this->id = $id;
		}	
	}
?>