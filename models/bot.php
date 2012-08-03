<?
	class Bot extends Model
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
		
		public function getJob()
		{
			return new Job($this->get('job_id'));
		}		
	}
?>
