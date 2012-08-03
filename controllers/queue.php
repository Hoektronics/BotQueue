<?
	class QueueController extends Controller
	{
		public function register()
		{
			$this->assertLoggedIn();
			
			$this->setTitle('Create a queue');
			
			if ($this->args('submit'))
			{
				//did we get a name?
				if (!$this->args('name'))
					$errors['name'] = "You need to provide a name.";
					
				//okay, we good?
				if (empty($errors))
				{
					//woot!
					$q = new Queue();
					$q->set('name', $name);
					$q->save();
					
					//todo: send a confirmation email.
					Activity::log("created a new queue named " . $q->getName(), $q);

					$this->forwardToUrl($q->getUrl() . '/view');
				}
				else
				{
					$this->set('errors', $errors);
					$this->setArg('name');
				}
			}
		}
		
		public function view()
		{
			//how do we find them?
			if ($this->args('id'))
				$q = new Queue($this->args('id'));

			//did we really get someone?
			if (!$q->isHydrated())
				$this->set('megaerror', "Could not find that queue.");
				
			//errors?
			if (!$this->get('megaerror'))
			{
				$this->set('queue', $q);
			}
		}
	}
?>
