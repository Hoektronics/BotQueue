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
