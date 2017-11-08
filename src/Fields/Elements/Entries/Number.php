<?php

namespace Dashifen\Form\Fields\Elements\Entries;

/**
 * Class Number
 *
 * @package Dashifen\Form\Fields\Elements\Entries
 */
class Number extends Text {
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		$field = parent::getField($display);
		
		// our parent can get us the attributes we want as a string that'll
		// look something like this:  step="1" min="0" max="10".  then, we
		// need to cram that into our $field.  it'll already be of type
		// number because of the name of the class.
		
		$field = $this->addAdditionalAttributes($field, ["step", "min", "max"]);
		return parent::display($field, $display);
	}
}
