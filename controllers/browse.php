<?
  /*
    This file is part of BotQueue.

    BotQueue is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BotQueue is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

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
			
			$total_pages = ceil($this->get('total') / $per_page);
			$min_page = max(1, $this->args('page') - 5);
			$max_page = min($total_pages, $this->args('page') + 5);

			//send new vars.
			$this->set('per_page', $per_page);
			$this->set('prev_page', $this->get('page') - 1);
			$this->set('next_page', $this->get('page') + 1);
			$this->set('total_pages', $total_pages);
			$this->set('min_page', $min_page);
			$this->set('max_page', $max_page);
			$this->set('fragment', $this->get('fragment'));
		}
	}
?>
