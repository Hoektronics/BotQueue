<?
	class Comment extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "comments");
		}
		
		public static function byGUID($guid)
		{
			//look up the token
			$sql = "
				SELECT id
				FROM comments
				WHERE guid = '$guid'
			";
			$id = db()->getValue($sql);
			
			//send it!
			return new Comment($id);
		}
    		
		public function getUrl()
		{
			return "/comment:{$this->id}";
		}
		
		public function getName()
		{
			return substr($this->get('comment_data'), 0, 32);
		}
	}
?>
