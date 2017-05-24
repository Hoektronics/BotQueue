<?php


class Wizard {
	private $forms;
	private $activeForm;
	private $nextFormActive;
	private $args;
	private $wizardMode;

	public $name;

	/**
	 * @param $name string
	 * @param $args
	 */
	public function __construct($name, $args) {
		$this->forms = array();
		$this->args = $args;
		$this->name = $name;
		$this->nextFormActive = true;
		$this->wizardMode = true;
	}

	/**
	 * @param $title string
	 * @param $form Form
	 * @param $redirect string
	 */
	public function add($title, $form, $redirect = null) {
		if(is_null($redirect)) {
			$redirect = $_SERVER["REQUEST_URI"];
		}
		if($this->nextFormActive) {
			$this->activeForm = $form->name;
			$this->nextFormActive = false;
		}
		if($form->checkSubmitAndValidate($this->args)) {
			$this->nextFormActive = true;
		}
		if(!empty($this->forms) && $this->wizardMode) {
			end($this->forms);
			$key = key($this->forms);
			/** @var Form $lastForm */
			$lastForm = $this->forms[$key]['form'];
			$lastForm->setSubmitText('Next');
		}
		$this->forms[$form->name] = array(
			'title' => $title,
			'form' => $form,
			'redirect' => $redirect
		);
	}

	public function disableWizardMode() {
		$this->wizardMode = false;
	}

	public function isFinished() {
		// If the next form is the active one, but no
		// other form has been added, then the wizard
		// is finished.
		return $this->nextFormActive;
	}

	public function render()
	{
		return Controller::byName('wizard')->renderView('view',
			array(
				'forms' => $this->forms,
				'name' => $this->name,
				'active' => $this->activeForm,
				'wizardMode' => $this->wizardMode
			)
		);
	}

} 