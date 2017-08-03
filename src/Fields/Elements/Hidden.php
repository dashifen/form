<?php

namespace Dashifen\Form\Fields\Elements;

use Dashifen\Form\Fields\AbstractField;

class Hidden extends AbstractField {
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		
		// a hidden field should be completely hidden, i.e. it doesn't
		// even need to be a part of a surrounding container.  it also
		// doesn't need any labels or instructions, so our format is pretty
		// simple.
		
		$format = '<input type="hidden" id="%s" name="%s" value="%s">';
		$field = sprintf($format, $this->getId(), $this->getName(), $this->getValue());
		return parent::display($field, $display);
	}
}
