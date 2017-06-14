<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

class SubmitButton extends Button {
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setExtraType("submit");
		$this->setIcon("fa-save");
	}
}
