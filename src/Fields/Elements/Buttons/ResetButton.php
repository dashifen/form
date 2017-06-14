<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

class ResetButton extends Button {
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setExtraType("reset");
		$this->setIcon("fa-undo");
	}
}
