<?
	class HTMLTemplateController extends Controller
	{
		public function main()
		{
			$this->setArg('content');
			$this->setArg('title');
			$this->setArg('sidebar');
		}
		
		public function header()
		{
			$this->setArg('title');
		}
		
		public function footer()
		{
			$this->setArg('sidebar');
		}
	}
?>
