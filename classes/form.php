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

	class Form
	{
		private $fields;
		private $data;
		
		public $name;
		public $action;
		public $method = 'POST';
		public $submitText = "Submit";
		
		public function __construct($name = 'form')
		{
			$this->name = $name;
			
			$this->add(new HiddenField(array(
				'name' => "{$this->name}_is_submitted",
				'value' => 1,
				'required' => true
			)));
		}

		public function checkSubmitAndValidate($data)
		{
			if ($this->isSubmitted($data))
				return $this->validate($data);
			return false;
		}
	
		public function isSubmitted($data)
		{
			return (boolean)$data["{$this->name}_is_submitted"];
		}
	
		public function add(FormField $field)
		{
			$this->fields[$field->name] = $field;
		}
		
		public function validate($data)
		{
			$rval = true;
		
			if (!empty($this->fields))
				foreach ($this->fields AS $field)
					if (!$field->validate($data))
						$rval = false;

			return $rval;
		}
		
		public function hasError()
		{
			if (!empty($this->fields))
				foreach ($this->fields AS $field)
					if ($field->error)
						return true;
			
			return false;
		}
		
		public function render($template = 'vertical')
		{
			return Controller::byName('form')->renderView($template . "_form", array('form' => $this));
		}
		
		public function renderFields()
		{
			$html = "";
			if (!empty($this->fields))
				foreach ($this->fields AS $field)
					$html .= $field->render();

			return $html;
		}
		
		public function data($name = null)
		{
		  if ($name === null)
		  {
        if (!empty($this->fields))
        {
          $data = array();
          foreach ($this->fields as $key => $field)
            $data[$key] = $field->getValue();
          
          return $data;
        }
		  }
		  else
		  {
  			if (isset($this->fields[$name]) && $this->fields[$name] instanceOf FormField)
  				return $this->fields[$name]->getValue();
		  } 
		  return null;
		}
	}
	
	class FormField
	{
		private $value;

		public $id;
		public $name;
		public $label;
		public $help;
		public $required = false;
		public $hasError = false;
		public $errorText;
				
		public function __construct($opts)
		{
			if (isset($opts['name']))
				$this->name = $opts['name'];
				
			if (isset($opts['id']))
				$this->id = $opts['id'];
			else
				$this->id = "i{$this->name}";
				
			if (isset($opts['value']))
				$this->setValue($opts['value']);
				
			if (isset($opts['label']))
				$this->label = $opts['label'];
				
			if (isset($opts['help']))
				$this->help = $opts['help'];				

			if (isset($opts['required']))
				$this->required = (boolean)$opts['required'];				
		}

		public function getValue()
		{
			return $this->value;
		}

		public function setValue($value)
		{
			$this->value = $value;
		}

		public function validate($data)
		{
			if ($this->required && !$data[$this->name])
			{
				$this->hasError = true;
				$this->errorText = "The {$this->label} field is required.";
			}
			
			//no error? pull in our data
			if (!$this->hasError)
				$this->setValue($data[$this->name]);

			//return false on error, true on success
			return !$this->hasError;
		}
		
		public function render()
		{
			return Controller::byName('form')->renderView(strtolower(get_class($this)), array('field' => $this));
		}
	}
	
	class HiddenField extends FormField
	{
	}
	
	class TextField extends FormField
	{
	}

	class TextareaField extends FormField
	{
	  public $width;
	  public $rows;
	  
	  public function __construct($opts)
		{
			if (isset($opts['width']))
				$this->width = $opts['width'];
				
			if (isset($opts['rows']))
				$this->rows = $opts['rows'];
			else
			  $this->rows = 4;
				
			parent::__construct($opts);
		}
	}

	class CheckboxField extends FormField
	{
	  //checkboxes have only 2 states and are valid no matter what.
	  public function validate($data)
		{
			$this->hasError = false;
      $this->setValue((int)$data[$this->name]);
			
			return true;
		}
	}
		
	class SelectField extends FormField
	{
		public $options;
		
		public function __construct($opts)
		{
			$this->options = $opts['options'];
			unset($opts['options']);
			
			parent::__construct($opts);
		}
	}
	
	class DisplayField extends FormField
	{		
		public function validate($data)
		{
			$this->hasError = false;

			return true;
		}
	}
?>