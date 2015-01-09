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

	public $name;
	public $action;
	public $method = 'POST';
	public $submitText = "Submit";
	public $submitClass = "btn btn-primary";
	private $externalForm = false;

	public function __construct($name = 'form', $externalForm = false)
	{
		$this->name = $name;
		$this->externalForm = $externalForm;

		if(!$externalForm) {
			$this->add(
				HiddenField::name($this->name."_is_submitted")
				->value(1)
				->required(true)
			);
		}
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

	/**
	 * @param FormField $field
	 */
	public function add(FormField $field)
	{
		$this->fields[$field->name] = $field;
	}

	public function get($name) {
		return $this->fields[$name];
	}

	/**
	 * @param $data
	 * @return bool
	 */

	public function validate($data)
	{
		$valid = true;

		if (!empty($this->fields))
			foreach ($this->fields AS $field)
				/* @var $field FormField */
				if (!$field->validate($data))
					$valid = false;

		return $valid;
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
		$rendered = array();
		if (!empty($this->fields))
			foreach ($this->fields AS $field)
				/* @var $field FormField */
				$rendered[] = $field->render();

		return implode("", $rendered);
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function data($name = null)
	{
		if ($name === null) {
			if (!empty($this->fields)) {
				$data = array();
				foreach ($this->fields as $key => $field)
					/* @var $field FormField */
					$data[$key] = $field->getValue();

				return $data;
			}
		} else {
			if (isset($this->fields[$name]) && $this->fields[$name] instanceOf FormField) {
				/* @var $field FormField */
				$field = $this->fields[$name];
				return $field->getValue();
			}
		}
		return null;
	}

	public function setSubmitText($text = 'Submit')
	{
		$this->submitText = $text;
	}

	public function setSubmitClass($class = 'btn btn-primary') {
		$this->submitClass = $class;
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
	public $attributes = array();

	// Valid Attribute fields
	public function onchange($callback)
	{
		$this->attributes['onchange'] = $callback;
		return $this;
	}

	public function onclick($callback)
	{
		$this->attributes['onclick'] = $callback;
		return $this;
	}

	public function ondblclick($callback)
	{
		$this->attributes['ondblclick'] = $callback;
		return $this;
	}

	public function onmousedown($callback)
	{
		$this->attributes['onmousedown'] = $callback;
		return $this;
	}

	public function onmousemove($callback)
	{
		$this->attributes['onmousemove'] = $callback;
		return $this;
	}

	public function onmouseover($callback)
	{
		$this->attributes['onmouseover'] = $callback;
		return $this;
	}

	public function onmouseout($callback)
	{
		$this->attributes['onmouseout'] = $callback;
		return $this;
	}

	public function onmouseup($callback)
	{
		$this->attributes['onmouseup'] = $callback;
		return $this;
	}

	public function onkeydown($callback)
	{
		$this->attributes['onkeydown'] = $callback;
		return $this;
	}

	public function onkeypress($callback)
	{
		$this->attributes['onkeypress'] = $callback;
		return $this;
	}

	public function onkeyup($callback)
	{
		$this->attributes['onkeyup'] = $callback;
		return $this;
	}

	public function onblur($callback)
	{
		$this->attributes['onblur'] = $callback;
		return $this;
	}

	public function onfocus($callback)
	{
		$this->attributes['onfocus'] = $callback;
		return $this;
	}

	public function onreset($callback)
	{
		$this->attributes['onreset'] = $callback;
		return $this;
	}

	public function onselect($callback)
	{
		$this->attributes['onselect'] = $callback;
		return $this;
	}

	public function id($id)
	{
		$this->id = $id;
		$this->attributes['id'] = $id;
		return $this;
	}

	public function value($value)
	{
		$this->value = $value;
		return $this;
	}

	public function label($label)
	{
		$this->label = $label;
		return $this;
	}

	public function help($text)
	{
		$this->help = $text;
		return $this;
	}

	public function required($required)
	{
		$this->required = $required;
		return $this;
	}

	/**
	 * @param $condition bool
	 * @param $text string
	 */
	public function error($text, $condition = true) {
		if($condition) {
			$this->hasError = true;
			$this->errorText = $text;
		}
	}

	/**
	 * @param $name string
	 */
	protected function __construct($name)
	{
		$this->name = $name;
		$this->attributes['name'] = $name;
	}

	public static function name($name)
	{
		return new static($name);
	}

	public function getAttributes()
	{
		//pull in our id
		if (!isset($this->attributes['id']))
			$this->attributes['id'] = $this->name;
		$this->id = $this->attributes['id'];

		$attribute_text = array();
		if (!empty($this->attributes))
			foreach ($this->attributes AS $key => $val)
				$attribute_text[] = "$key=\"$val\"";

		return implode(" ", $attribute_text);
	}

	public function getValue()
	{
		return $this->value;
	}

	public function validate($data)
	{
		if ($this->required && (!array_key_exists($this->name, $data) || $data[$this->name] === '')) {
			$this->error("The {$this->label} field is required.");
		}

		//no error? pull in our data
		if (!$this->hasError) {
			// sanitize our data to prevent XSS
			$sanitized_name = filter_var($data[$this->name], FILTER_SANITIZE_STRING);
			if ($sanitized_name === $data[$this->name]) {
				$this->value($data[$this->name]);
			} else {
				$this->error("Sorry, no HTML in this field, please");
			}
		}

		//return false on error, true on success
		return !$this->hasError;
	}

	public function render()
	{
		return Controller::byName('form')->renderView(strtolower(get_class($this)), array('field' => $this));
	}
}

class HiddenField extends FormField {}

class TextField extends FormField {}

class NumericField extends FormField
{
	private $min = null;
	private $max = null;
	public function min($num) {
		if(!is_numeric($num))
			throw new Exception("Not a valid minimum");
		$this->min = $num;
		return $this;
	}

	public function max($num) {
		if(!is_numeric($num))
			throw new Exception("Not a valid maximum");
		$this->max = $num;
		return $this;
	}

	public function validate($data) {
		$num = $data[$this->name];
		if(!is_numeric($num)) {
			$this->error("That is not a valid number.");
		} else if(!is_null($this->min) && $num < $this->min) {
			$this->error("That number is too small");
		} else if(!is_null($this->max) && $num > $this->max) {
			$this->error("That number is too large");
		} else {
			parent::validate($data);
		}
		return !$this->hasError;
	}
}

class PasswordField extends TextField {}

class EmailField extends TextField
{
	public function validate($data) {
		$email = $data[$this->name];
		$filtered_email = filter_var($email, FILTER_VALIDATE_EMAIL);
		if($filtered_email != $email) {
			$this->error("You must supply a valid email.");
		} else {
			parent::validate($data);
		}

		return !$this->hasError;
	}
}

class UrlField extends TextField
{
	public function validate($data) {
		$url = $data[$this->name];
		$filtered_url = filter_var($url, FILTER_VALIDATE_URL);
		if($filtered_url != $url) {
			$this->error("You must supply a valid URL.");
		} else {
			parent::validate($data);
		}

		return !$this->hasError;
	}
}

class TextareaField extends FormField
{
	public $width;
	public $rows;

	protected function __construct($name)
	{
		$this->rows = 4;
		parent::__construct($name);
	}

	public function rows($rows)
	{
		$this->rows = $rows;
		return $this;
	}

	public function width($width)
	{
		$this->width = $width;
		return $this;
	}
}

class CheckboxField extends FormField
{
	public $is_checked = true;

	//checkboxes have only 2 states and are valid no matter what.
	public function validate($data)
	{
		$this->hasError = false;
		$this->value((int)$data[$this->name]);

		return true;
	}

	public function checked($is_checked = true) {
		$this->is_checked = $is_checked;
		return $this;
	}
}

class SelectField extends FormField
{
	public $options = array();

	/**
	 * @param $options array
	 * @return $this
	 */
	public function options($options)
	{
		$this->options = $options;
		return $this;
	}

	public function option($option)
	{
		$this->options[] = $option;
		return $this;
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

class LinkField extends FormField
{
	public $link;

	public function link($link) {
		$this->link = $link;
		return $this;
	}
}

class UploadField extends FormField
{
	public function validate($data)
	{
		//upload our file
		$file = $_FILES[$this->name];

		//double check for errors.
		if ($file['size'] == 0 && $file['error'] == 0)
			// todo: Move the error code so we can use UPLOAD_ERR_EMPTY
			$file['error'] = 9; // UPLOAD_ERR_EMPTY

		//set our value for future reference.
		$this->value($file);

		//what did we get?
		if ($file['error'] && $this->required) {
			$this->error(Utility::$upload_errors[$file['error']]);
		}

		//how did we do?
		return !$this->hasError;
	}
}

class RawField extends DisplayField {}

class WarningField extends DisplayField {}

class ErrorField extends DisplayField {}

class SuccessField extends DisplayField {}

class InformationField extends DisplayField {}

class GoogleCaptcha extends FormField
{
	public function validate($data)
	{
		$url = "https://www.google.com/recaptcha/api/siteverify".
		"?secret=".GOOGLE_CAPTCHA_SECRET_KEY.
		"&response=".$data['g-recaptcha-response'].
		"&remoteip=".$_SERVER['REMOTE_ADDR'];
		$result = json_decode(file_get_contents($url), true);

		return $result['success'];
	}
}