<?php

namespace Dashifen\Form\Fields\Elements\Selections;
use Dashifen\Form\Fields\FieldException;

/**
 * Class SelectOneWithOther
 *
 * The SelectOneWithOther class is a select element with an input[type=text]
 * field immediately after it.  That field is displayed when the select's
 * value is "?" and hidden otherwise.
 *
 * @package Dashifen\Form\Fields\Elements\Selections
 */
class SelectOneWithOther extends SelectOne {
	/**
	 * @var string
	 */
	protected $other = "";
	
	/**
	 * @return string
	 */
	public function getOther(): string {
		return $this->other;
	}
	
	/**
	 * @param string $other
	 */
	public function setOther(string $other) {
		$this->other = $other;
	}
	
	/**
	 * @return string
	 */
	protected function getDefaultDisplay(): string {
	
		// our parent will determine the default display based on the number
		// of options to display.  here, though, we just want a <select>
		// element to facilitate easy identification of when things change.

		return "select";
	}
	
	/**
	 * @return array
	 */
	public function getClasses(): array {
		
		// we want to be sure that we have our with-other class but then
		// our parent can take care of the rest.
		
		$this->addWithOtherClass();
		return parent::getClasses();
	}
	
	/**
	 * @return void
	 */
	protected function addWithOtherClass(): void {
	
		// if our with-other class is not already a part of this object,
		// then we'll add it here.  this function may get called a few times,
		// but we only add it if needed the first time.

		if (!in_array("with-other", $this->classes)) {
			$this->classes[] = "with-other";
		}
	}
	
	/**
	 * @return string
	 */
	public function getClassesAsString(): string {
		
		// like when getting an array of our classes, we want to be sure
		// that we have our with-other class, but then the SelectOne's
		// behavior is good enough.
		
		$this->addWithOtherClass();
		return parent::getClassesAsString();
	}
	
	/**
	 * @param string $value
	 *
	 * @return void
	 * @throws FieldException
	 */
	public function setValue(string $value): void {
		
		// when setting the value for this element, we might have a JSON
		// string defining both the selection's value and the other.  or,
		// it might just be the value.  we can check as follows.
		
		$temp = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE) {
			
			// if we don't have a JSON error, then we'll assume that we have
			// both a value and an other and handle things accordingly.
			
			if (sizeof($temp) >= 2) {
				parent::setValue($temp[0]);
				$this->setOther($temp[1]);
			} else {
				throw new FieldException(
					"Setting a SelectOneWithOther's value requires both an index and other value.",
					FieldException::NOT_ENOUGH_VALUES
				);
			}
		} else {
			
			// otherwise, we just use $value as this fields value, and we
			// leave the other alone for the moment.
			
			parent::setValue($value);
		}
	}
	
	/**
	 * @return string
	 */
	public function getValue(): string {
		
		// the normal SelectOne's value is just the value property, but for
		// this element, we need to send back the other property, too.
		
		return json_encode([$this->value, $this->other]);
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		$field = parent::getField(false);
		
		// to construct this field, we have to add an input[type=text] field
		// following the select element that $field currently contains.
		
		$format = '<input type="text" id="%s-other" name="%s-other" class="%s other other-hidden" value="%s">';
		$input = sprintf($format, $this->id, $this->name, $this->getClassesAsString(), $this->getOther());
		$field = str_replace("</select>", "$input</select>", $field);
		
		// but, that's not quite enough; we also need to add the behavior that
		// toggles the display of our other field.  we'll add an onchange
		// attribute to our select element and some JS to our DOM as follows.
		
		$jsFunction = uniqid("selectWithOther_");
		$replacement = '<select onchange="' . $jsFunction . '(this)" ';
		$field = str_replace("<select ", $replacement, $field);
		$field .= $this->getJavaScript($jsFunction);
		
		return parent::display($field, $display);
	}
	
	/**
	 * @param $functionName
	 *
	 * @return string
	 */
	protected function getJavaScript($functionName) {
		
		// our javascript is pretty simple for this behavior.  the function
		// receives the select element as a parameter which we can use to
		// quickly identify the other element and its value.  then, if the
		// value is "?" we want to remove the other-hidden and add it other-
		// wise.  the toggle() method of the classList object handles that.
		
		return <<<JAVASCRIPT
			function $functionName(select) {
				var other = select.nextElementSibling;
				var value = select.options[select.selectedIndex].value;
				other.classList.toggle("other-hidden", value !== "?");
			}
JAVASCRIPT;
	
	}
}