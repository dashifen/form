<?php

namespace Dashifen\Form\Fields\Elements\Entries;

use Dashifen\Form\Fields\FieldException;

class File extends Text {
	/**
	 * @param bool $display
	 *
	 * @return string
	 * @throws FieldException
	 */
	public function getField(bool $display = false): string {
		$input = parent::getField($display);
		
		// a file input is basically the same as a text input except that
		// we can't set the value of a file input at all.  so, instead, if
		// there's a value for this field, we'll add it after the <input>
		// and before the <li> closing tag.
		
		if (!empty($this->value)) {
			$format = '<span class="file-field-value">%s <em>%s</em></span>';
			$value = sprintf($format, $this->getValueLabel(), $this->value);
			$input = str_replace("</li>", "$value</li>", $input);
		}
		
		return $input;
	}
	
	/**
	 * @return string
	 */
	protected function getValueLabel(): string {
		return "Current file:";
	}
}
