<?php

namespace Dashifen\Form\Fields\Elements;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\FieldException;

class Hidden extends AbstractField {
	/**
	 * @param bool $display
	 *
	 * @return string
	 * @throws FieldException
	 */
	public function getField(bool $display = false): string {
		
		// a hidden field should be completely hidden, i.e. it doesn't
		// even need to be a part of a surrounding container.  it also
		// doesn't need any labels or instructions, so our format is pretty
		// simple.
		
		$format = '<input type="hidden" id="%s" name="%s" class="%s" value="%s">';
		$field = sprintf($format, $this->getId(), $this->getName(), $this->getInputClassesAsString(), $this->getValue());
		return parent::display($field, $display);
	}
}
