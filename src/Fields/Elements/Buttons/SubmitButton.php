<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

/**
 * Class SubmitButton
 *
 * @package Dashifen\Form\Fields\Elements\Buttons
 */
class SubmitButton extends Button {
	/**
	 * SubmitButton constructor.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 */
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setExtraType("submit");
		$this->setIcon("fa-save");
	}
}
