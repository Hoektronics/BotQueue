<?
	class Job extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "bots");
		}

		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getUrl()
		{
			return "/bot:" . $this->id;
		}
		
		public function getFile()
		{
			return new S3File($this->get('file_id'));
		}		

		public function getQueue()
		{
			return new S3File($this->get('queue_id'));
		}		

		public function getBot()
		{
			return new Bot($this->get('bot_id'));
		}
	}
?>
