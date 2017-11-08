<?php

namespace Dashifen\Form\Fields\Elements\Entries;

/**
 * Class Honeypot
 *
 * @package Dashifen\Form\Fields\Elements\Entries
 */
class Honeypot extends Text {
	/**
	 * Honeypot constructor.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 */
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
	
		// by default, our parent's constructor would set the type of this
		// field to "honeypot" and that's not a real HTML input type.  so,
		// we need to do a little more work before we're done.  we can also
		// set our default instructions.
		
		$instructions = <<<INSTRUCTIONS
		
			If you're encountering this field, we apologize.  It's used
			to try and stop bots from submitting this form, and it must
			remain blank.  We've tried to hide it from  legitimate (and
			welcome) visitors, like you, but it's not a foolproof thing.
			Hide it too well, and the illegitimate visitors might be able
			to slip by as well.  Regardless, please leave this one blank
			when you submit the form.
			
INSTRUCTIONS;
		
		$this->setInstructions($instructions);
		$this->setType("text");
		
		// to avoid overwriting the above instructions during the parsing of
		// a honeypot field, we're going to lock our field.
		
		$this->locked = true;
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		
		// the only thing we want to do here is remove the honeypot field
		// from the tab order for the form.  we use a quick string replace
		// to make that happen.  this is a similar operation to how we
		// do other additional attributes, but this time we want to add it
		// regardless of what anyone else tells us to do.
		
		return str_replace("<input", '<input tabindex="-1"', parent::getField($display));
	}
}
