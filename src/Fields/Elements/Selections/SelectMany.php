<?php

namespace Dashifen\Form\Fields\Elements\Selections;
use Dashifen\Form\Fields\FieldException;

/**
 * Class SelectMany
 *
 * Our SelectMany selection is very similar to the SelectOne
 * field.  we can extend that one to build this one and override
 * some of its methods here to make this one work.
 *
 * @package Dashifen\Form\Fields\Elements\Selections
 */
class SelectMany extends SelectOne {
	/**
	 * @var array $values
	 */
	protected $values = null;
	
	/**
	 * @return string
	 */
	protected function getDefaultDisplay(): string {
		
		// our SelectMany element can be either a fieldset of checkboxes,
		// or a <select> element with the multiple flag set.  by default,
		// we go with a fieldset.
		
		return "fieldset";
	}
	
	/**
	 * @return string
	 */
	protected function getInputsAsString(): string {
		$radios = parent::getInputsAsString();
		
		// the SelectOne field returns its fieldset of input elements
		// as radio buttons.  changing those to checkboxes is fairly
		// easy:  we change the type and we need to be sure that the
		// name field posts an array.  the first one is easy:
		
		$checkboxes = str_replace('type="radio"', 'type="checkbox"', $radios);
		
		// for the second one we need to make a regular expression to
		// do what we need to do.  we know the name is in the form of
		// name="X" where X is the name.  so, we can use the following
		// pattern and preg_replace() to do our work.
		
		$checkboxes = preg_replace("/(name=\"[^\"]+)/", '$1[]', $checkboxes);
		return $checkboxes;
	}
	
	protected function getFieldAsSelect(): string {
		$select = parent::getFieldAsSelect();
		
		// our parent produces a <select> field with all our options
		// in it almost exactly as we need to with two exceptions: we
		// need to set the multiple flag and set a size attribute.  at
		// the moment, we don't have a good way to pass a user-supplied
		// size into the function so we simply show 50% of our options
		// or 10 which ever is smaller.
		
		$optionCount = sizeof($this->options);
		$size = ($count = floor($optionCount/2)) < 10 ? $count : 10;
		$replacement = sprintf('<select size="%s" multiple ', $size);
		return str_replace('<select', $replacement, $select);
	}
	
	/**
	 * @param string $optionValue
	 *
	 * @return bool
	 */
	protected function isSelected(string $optionValue): bool {
		
		// this field's value is an array (technically, a JSON string).  the
		// transformValues() method will take that string and make us our array
		// of values.  then, the in_array() function will take us home.
		
		return in_array($optionValue, $this->transformValues());
	}
	
	/**
	 * @return array
	 * @throws FieldException
	 */
	protected function transformValues(): array {
		if ($this->isEmpty()) {
			return [];
		}
		
		// if we've already transformed our values before, we'll return
		// what we found last time.  this should save a little time since
		// this method will likely end up being called via a loop as we
		// iterate over our option values.
		
		if (!is_null($this->values)) {
			return $this->values;
		}
		
		// our value should be a JSON string representing the many values for
		// this field.  but, we need to decode it.  if we don't run into any
		// JSON errors, we're good to go.  if we do, we throw an exception.
		
		$values = json_decode($this->value, true);
		if (json_last_error() === JSON_ERROR_NONE) {
			return ($this->values = $values);
		}
		
		throw new FieldException("SelectMany requires JSON value.",
			FieldException::INVALID_VALUE);
	}
}
