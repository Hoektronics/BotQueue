<?php
	class Consumer extends Model
	{
		public static function findByKey($key){
			$consumer = null;
			$pdo = Db::singleton();
			$info = $pdo->query("select id from consumer where consumer_key = '".$key."'"); // this is not safe !
			if($info->rowCount()==1){
				$info = $info->fetch();
				$consumer = new Consumer($info['id']);
			}
			return $consumer;
		}
		
		public function __construct($id = 0){
			$this->pdo = Db::singleton();
			if($id != 0){
				$this->id = $id;
				$this->load();
			}
		}
		
		private function load(){
			$info = $this->pdo->query("select * from consumer where id = '".$this->id."'")->fetch();
			$this->id = $this->id;
			$this->key = $info['consumer_key'];
			$this->secret = $info['consumer_secret'];
			$this->active = $info['active'];
		}
		
		public static function create($key,$secret){
			$pdo = Db::singleton();
			$pdo->exec("insert into consumer (consumer_key,consumer_secret,active) values ('".$key."','".$secret."',1)");
			$consumer = new Consumer($pdo->lastInsertId());
			return $consumer;
		}
		
		public function isActive(){
			return $this->active;
		}
		
		public function getKey(){
			return $this->key;
		}
		
		public function getSecretKey(){
			return $this->secret;
		}
		
		public function getId(){
			return $this->id;
		}
		
		public function hasNonce($nonce,$timestamp){
			$check = $this->pdo->query("select count(*) as cnt from consumer_nonce where timestamp = '".$timestamp."' and nonce = '".$nonce."' and consumer_id = ".$this->id)->fetch();
			if($check['cnt']==1){
				return true;
			} else {
				return false;
			}
		}
		
		public function addNonce($nonce){
			$check = $this->pdo->exec("insert into consumer_nonce (consumer_id,timestamp,nonce) values (".$this->id.",".time().",'".$nonce."')");
		}
		
		/* setters */
		
		public function setKey($key){
			$this->key = $key;
		}
		
		public function setSecret($secret){
			$this->secret = $secret;
		}
		
		public function setActive($active){
			$this->active = $active;
		}
		
		public function setId($id){
			$this->id = $id;
		}
		
	}
	
?>
