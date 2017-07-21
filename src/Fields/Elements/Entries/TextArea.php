<?php

namespace Dashifen\Form\Fields\Elements\Entries;

use Dashifen\Form\Fields\AbstractField;

/**
 * Class TextArea
 *
 * @package Dashifen\Form\Fields\Elements\Entries
 */
class TextArea extends AbstractField {
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		$format = '
			<li class="%s">
				%s
				%s
				<textarea id="%s" name="%s" class="%s" aria-required="%s"%s>%s</textarea>
			</li>
		';
		
		// like our Text field, this one has a lot of details that get
		// crammed into our format with sprintf().  they are as follows:
		// the <li> classes, the label, the instructions, and then the
		// attribute values as specified in the string above.  the
		// final %s before the closing bracket of the <textarea> tag
		// is for the old-school required flag.
		
		$textarea = sprintf($format,
			$this->getLiClass(),
			$this->getLabel(),
			$this->getVerboseInstructions(),
			$this->getId(),
			$this->getName(),
			$this->getClassesAsString(),
			$this->getRequired() ? "true" : "false",
			$this->getRequired() ? " required" : ""
		);
		
		return parent::display($textarea, $display);
	}
	
}
