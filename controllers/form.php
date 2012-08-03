<?
	class FormController extends Controller
	{
		private function _global()
		{
			$this->setArg('name');
			$this->setArg('value');
			$this->setArg('error');

			$id = $this->args('id');
			if ($id)
				$this->set('id', "id=\"$id\"");
								
			$onchange = $this->args('onchange');
			if ($onchange)
				$this->set('onchange', "onchange=\"$onchange\"");
		}
	
		public function selectfield()
		{
			$this->_global();
			
			$this->setArg('options');
		}
	}
?>
