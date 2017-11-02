<?php

namespace Dashifen\Form\Fields\Elements\Selections;

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
		// easy:  we change some instances of the word "radio" to
		// "checkbox" and we need to rename each input's name so that
		// we submit an array of checked boxes.
		
		$checkboxes = str_replace('type="radio"', 'type="checkbox"', $radios);
		$checkboxes = str_replace('class="radio', 'class="checkbox', $checkboxes);
		
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
		
		return in_array($optionValue, $this->transformJsonValue());
	}
}
