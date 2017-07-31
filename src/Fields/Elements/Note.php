<?php

namespace Dashifen\Form\Fields\Elements;

use Dashifen\Form\Fields\AbstractField;

class Note extends AbstractField {
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		
		// a note is a very simple element - in fact, it's just words.
		// its purpose is to share instructions with a visitor about a
		// form somewhere other than at the top of a fieldset.  to that
		// end, we can just include the text-based properties as a part
		// of the field as follows:
		
		$format = <<< FIELD
			<li class="%s">
				%s
				%s
			</li>
FIELD;
		
		$field = sprintf($format, $this->getLiClass(),
			$this->getVerboseInstructions());
		
		return parent::display($field, $display);
	}
}
