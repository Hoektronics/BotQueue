<?
	class BrowseController extends Controller
	{
		public function pagination_info()
		{
			//a little bit of prep.
			$per_page = $this->args('per_page');
			if (!$per_page)
				$per_page = 15;
				
			//whats our noun?
			$word = $this->args('word');
			if (!$word)
				$word = 'thing';
			
			//figure out all the stuff.
      $start = (($this->args('page') - 1) * $per_page) + 1;
			if ($start < 0)
				$start = 0;
			
			$end = $this->args('page') * $per_page;
			$end = min($end, $this->args('total'));

			//pass thru our args.
			$this->setArg('total');
			$this->setArg('page');
			$this->setArg('per_page');
			$this->set('start', $start);
			$this->set('end', $end);
			$this->set('word', $word);
		}
		
		public function pagination()
		{
			//a little bit of prep.
			$per_page = $this->args('per_page');
			if (!$per_page)
				$per_page = 15;
			
			//pass thru our args.
			$this->setArg('total');
			$this->setArg('page');
			$this->setArg('base_url');
			$this->setArg('fragment');

			//send new vars.
			$this->set('per_page', $per_page);
			$this->set('prev_page', $this->get('page') - 1);
			$this->set('next_page', $this->get('page') + 1);
			$this->set('max_page', ceil($this->get('total') / $per_page));
			$this->set('fragment', $this->get('fragment'));
		}
	}
?>
