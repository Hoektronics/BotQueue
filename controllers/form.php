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
		public function vertical_form()
		{
			$this->_form();
		}
		
		public function hiddenfield()
		{
			$this->_field();
		}

		public function textfield()
		{
			$this->_field();
		}
		
		public function checkboxfield()
		{
			$this->_field();
		}
		
		public function selectfield()
		{
			$this->_field();
		}

		public function displayfield()
		{
			$this->_field();
		}

		private function _form()
		{
			$this->setArg('form');
		}

		private function _field()
		{
			$this->setArg('field');
		}
	}
?>
