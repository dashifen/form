<?php

namespace Dashifen\Form\Fields\Elements\Entries;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\FieldException;

/**
 * Class Text
 *
 * @package Dashifen\Form\Fields\Elements\Entries
 */
class Text extends AbstractField {
	/**
	 * @param bool $display
	 *
	 * @return string
	 * @throws FieldException
	 */
	public function getField(bool $display = false): string {
		$format = '
			<li class="%s">
				%s
				%s
				<input type="%s" id="%s" name="%s" class="%s" value="%s" aria-required="%s"%s>
			</li>
		';
		
		// the sprintf "fields" in the above format are as follows:  the
		// item classes, the element's label, its instructions, and then
		// its attribute as labeled in the HTML.  the final field before
		// the closing bracket is for the old-school "required" flag.
		
		$field = sprintf($format,
			$this->getLiClass(),
			$this->getLabel(),
			$this->getVerboseInstructions(),
			$this->getType(),
			$this->getId(),
			$this->getName(),
			$this->getInputClassesAsString(),
			$this->getValue(),
			$this->getRequired() ? "true" : "false",
			$this->getRequired() ? " required" : ""
		);
		
		$field = $this->addAdditionalAttributes($field, "input", ["maxlength"]);
		return parent::display($field, $display);
	}
}
