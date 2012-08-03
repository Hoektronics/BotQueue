<?
	class EmailController extends Controller
	{
		public function lost_pass()
		{
			$this->setArg('user');
			$this->setArg('link');
		}
		
		public function lost_pass_html()
		{
			$this->lost_pass();
		}
	}
?>
