<?
	class MainController extends Controller
	{
		public function home()
		{
      $this->setTitle('Home');
			$this->setSidebar(Controller::byName('main')->renderView('sidebar'));
			
			$collection = Activity::getStream();
      $per_page = 25;
      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
      $this->set('per_page', $per_page);
      $this->set('total', $collection->count());
      $this->set('page', $page);
      $this->set('activities', $collection->getPage($page, $per_page));
		}
		
		public function activity()
		{
			$this->setTitle('Activity Log');
			
			$collection = Activity::getStream();
      $per_page = 25;
      $page = $collection->putWithinBounds($this->args('page'), $per_page);
    
      $this->set('per_page', $per_page);
      $this->set('total', $collection->count());
      $this->set('page', $page);
      $this->set('activities', $collection->getPage($page, $per_page));
		}
		
		public function draw_activities()
		{
			$this->setArg('activities');
		}
		
		public function sidebar()
		{
		}
		
		public function viewmode()
		{
			$mode = $this->args('view_mode');
			setcookie('viewmode', $mode, time()+60*60*24*30, '/');			
			$this->forwardToUrl('/');
		}
		
		public function shortcode()
		{
			$code = ShortCode::byCode($this->args('code'));
			
			die($code->get('url'));
		}
	}
?>
