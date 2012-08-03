<?
	class Activity extends Model
	{
		public function __construct($id = null)
		{
			parent::__construct($id, "activities");
		}
		
		public static function getStream() {
			$sql = "
				SELECT id, user_id
				FROM activities
				ORDER BY id DESC
			";

		  return new Collection($sql, array(
		    'User'      => 'user_id',
		    'Activity'  => 'id'
		  ));
		}
		
		public static function log($activity, $user = null)
		{
			if ($user === null)
				$user = User::$me;
				
			$a = new Activity();
			$a->set('user_id', $user->id);
			$a->set('action_date', date("Y-m-d H:i:s"));
			$a->set('activity', $activity);
			$a->save();
			
			return $a;
		}
	}
?>
