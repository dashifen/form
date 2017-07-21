<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

/**
 * Class ResetButton
 *
 * @package Dashifen\Form\Fields\Elements\Buttons
 */
class ResetButton extends Button {
	/**
	 * ResetButton constructor.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 */
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setExtraType("reset");
		$this->setIcon("fa-undo");
	}
}
