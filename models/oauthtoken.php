<?php
	class OAuthToken extends Model
	{	
		protected $id;
		protected $type;
		protected $consumer;
		protected $token;
		protected $token_secret;
		protected $callback;
		protected $verifier;
		protected $user;
		protected $pdo;
		
		/* static functions */
		
		public static function createRequestToken(IConsumer $consumer,$token,$tokensecret,$callback){
			$pdo = Db::singleton();
			$pdo->exec("insert into token (type,consumer_id,token,token_secret,callback_url) values (1,".$consumer->getId().",'".$token."','".$tokensecret."','".$callback."') ");
		}
		
		public static function findByToken($token){
			$ret = null;
			$pdo = Db::singleton();
			$find = $pdo->query("select id from token where token = '".$token."'");
			if($find->rowCount()==1){
				$find = $find->fetch();
				$request_token = new Token($find['id']);
				$ret = $request_token;
			}
			return $ret;
		}
		
		
		public function __construct($id=0){
			$this->pdo = Db::singleton();
			if($id != 0){
				$this->id = $id;
				$this->load();
			}
		}
		
		private function load(){
			$info = $this->pdo->query("select * from token where id = ".$this->id)->fetch();
			$this->token = $info['token'];
			$this->type = $info['type'];
			$this->token_secret = $info['token_secret'];
			$this->consumer = new Consumer($info['consumer_id']);
			$this->callback = $info['callback_url'];
			$this->verifier = $info['verifier'];
			if($info['user_id'] != 0){
				$this->user = new User($info['user_id']);
			} else {
				$this->user = 0;
			}
		}
		
		public function changeToAccessToken($token,$secret){
			if($this->isRequest()){
				$this->pdo->exec("update token set type = 2, verifier = '', callback_url = '', token = '".$token."', token_secret = '".$secret."' where id = ".$this->id);	
				return true;
			} else {
				return false;
			}
		}
		
		/* some setters */
		
		public function setVerifier($verifier){
			$this->pdo->exec("update token set verifier = '".$verifier."' where id = ".$this->id);
			$this->verifier = $verifier;
		}
		
		public function setUser(IUser $user){
			$this->pdo->exec("update token set user_id = '".$user->getId()."' where id = ".$this->id);
			$this->user = $user;
		}
		
		/* some getters */
		
		public function isRequest(){
			return $this->type == 1;
		}
		
		public function isAccess(){
			return !$this->isRequest();
		}
		
		public function getCallback(){
			return $this->callback;
		}
		
		public function getVerifier(){
			return $this->verifier;
		}
		
		public function getType(){
			return $this->type;
		}
		
		public function getSecret(){
			return $this->token_secret;
		}
		
		public function getUser(){
			return $this->user;
		}
		
	}
?>