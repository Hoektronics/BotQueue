<?php
	class OAuthConsumer extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "oauth_consumer");
		}

		public static function findByKey($key)
		{
			$sql = "
				SELECT id
				FROM oauth_consumer
				WHERE consumer_key = '". mysql_real_escape_string($key) ."'
			";
			$id = db()->getValue($sql);

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
					AND consumer_id = ". mysql_real_escape_string($this->id) ."
			");

			return ($check==1);
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
		
		public function delete()
		{
			//delete all our tokens
			db()->execute("
				DELETE FROM oauth_token WHERE consumer_id = ". mysql_real_escape_string($this->id) ."
			");

			//delete all our nonces
			db()->execute("
				DELETE FROM oauth_token_nonce WHERE consumer_id = ". mysql_real_escape_string($this->id) ."
			");
			
			parent::delete();
		}
	}
?>