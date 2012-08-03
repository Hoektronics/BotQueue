<?
	class Queue extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "queues");
		}
		
		public function getName()
		{
			return $this->get('name');
		}

		public function getUser()
		{
			return new User($this->get('user_id'));
		}
		
		public function getUrl()
		{
			return "/queue:" . $this->id;
		}
		
		public function getJobs()
		{
			$sql = "
				SELECT id
				FROM jobs
				WHERE queue_id = '{$this->id}'
				ORDER BY user_sort
			";
			return new Collection($sql, array('Job' => 'id'));
		}
	}
?>
