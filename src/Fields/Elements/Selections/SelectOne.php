<?php

namespace Dashifen\Form\Fields\Elements\Selections;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\FieldException;
use Dashifen\Form\Fields\Traits\TypeTrait;

class SelectOne extends AbstractField {
	use TypeTrait;
	
	/**
	 * @var array
	 */
	protected $extraTypes = ["select", "fieldset"];
	
	
	/**
	 * Sets the $extraType parameter.
	 * Must be either select or radio to determine display.
	 *
	 * @param string $extraType
	 */
	public function setExtraType(string $extraType): void {
		if (!in_array($extraType, $this->extraTypes)) {
			$extraType = "";
		}
		
		$this->extraType = $extraType;
	}
	
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		
		// a SelectOne field is either a select element or a radio button
		// set.  if our extra type property is set to one or the other, then
		// we'll follow that directive; otherwise, we'll get the default
		// display with the following method.
		
		if (!in_array($this->extraType, $this->extraTypes)) {
			$this->extraType = $this->getDefaultDisplay();
		}
		
		// now, we want to call either getFieldAsSelect or getFieldAsRadio
		// below.  we can build that function name using our extra type
		
		$field = $this->{"getFieldAs" . ucfirst($this->extraType)}();
		return parent::display($field, $display);
	}
	
	protected function getDefaultDisplay(): string {
		
		// if a programmer has not specified which display to use, we
		// want to return the default option here.  the default is
		// determined by the number of options:  5 or more and we use
		// a <select> element.
		
		return sizeof($this->options) >= 5 ? "select" : "fieldset";
		
	}
	
	/**
	 * @return string
	 */
	protected function getFieldAsSelect(): string {
		
		// a select field is just that: a <select> field.  first, we'll
		// mess with our options to build the contents of the <select>.
		// then, we'll build the <select> itself.
		
		$options = $this->getOptionsAsString();
		
		$format = '
			<li class="%s">
				%s
				%s
				<select id="%s" name="%s" class="%s" aria-required="%s"%s>
					%s
				</select>
			</li>
		';
		
		return sprintf($format,
			$this->getLiClass(),
			$this->getLabel(),
			$this->getVerboseInstructions(),
			$this->getId(),
			$this->getName(),
			$this->getClassesAsString(),
			$this->required ? "true" : "false",
			$this->required ? " required" : "",
			$options
		);
	}
	
	/**
	 * @return string
	 * @throws FieldException
	 */
	protected function getOptionsAsString(): string {
		
		
		// first we want to validate our options, which we also need to do
		// when building a radio button set.  so, we've moved that validation
		// to the validateOptions() method.  that method throws exceptions
		// which we could catch here, but we'd only re-throw them, so we'll
		// just call the method and, if we make it back here without an
		// exception, we can return our groups.  for our convenience, that
		// method returns the depth of the array which we need to know about
		// here.
		
		$depth = $this->validateOptions(2);
		
		return $depth === 1
			? $this->getUngroupedOptions()
			: $this->getGroupedOptions();
	}
	
	protected function validateOptions(int $acceptableDepth): int {
		// we have to check for two problems:  a complete lack of options
		// and an option array with more than two dimensions.
		
		if (sizeof($this->options) === 0) {
			throw new FieldException("Cannot build selection: no options.", FieldException::OPTIONS_REQUIRED);
		}
		
		$depth = $this->getArrayDepth($this->options);
		
		if ($depth > $acceptableDepth) {
			throw new FieldException("Cannot build selection: options too deep.", FieldException::OPTIONS_TOO_DEEP);
		}
		
		return $depth;
	}
	
	/**
	 * @param array $array
	 *
	 * @return int
	 */
	protected function getArrayDepth(array $array): int {
		// source: http://stackoverflow.com/a/263621
		
		$max_indentation = 1;
		$array_str = print_r($array, true);
		$lines = explode("\n", $array_str);
		
		foreach ($lines as $line) {
			$indentation = (strlen($line) - strlen(ltrim($line))) / 4;
			
			if ($indentation > $max_indentation) {
				$max_indentation = $indentation;
			}
		}
		
		return (int)ceil(($max_indentation - 1) / 2) + 1;
	}
	
	/**
	 * @param array $originals
	 *
	 * @return string
	 */
	protected function getUngroupedOptions(array $originals = []): string {
		
		// in order to use this method not just for our options property but
		// for the list of options within groups created in the next method,
		// if our argument is empty, we set it to the property.  this lets
		// a single method handle both of these needs since the behavior is,
		// overall, the same but operates on different data.
		
		if (empty($originals)) {
			$originals = $this->options;
		}
		
		// getting ungrouped options is fairly simple.  we just need to
		// take each item in the options array and turn it into an <option>
		// element.  then, we return the concatenation of them.
		
		$options = [];
		$format = '<option value="%s"%s>%s</option>';
		foreach ($originals as $value => $text) {
			$selected = $this->isSelected($value) ? " selected" : "";
			$options[] = sprintf($format, $value, $selected, $text);
		}
		
		return join("", $options);
	}
	
	/**
	 * @return string
	 */
	protected function getGroupedOptions(): string {
		
		// for grouped options, we need to make not only a list of <options>
		// but they go into <optgroup> elements, too.  luckily, most of our
		// work can be handled by the prior method here.
		
		$groups = [];
		$format = '<optgroup label="%s">%s</optgroup>';
		foreach ($this->options as $group => $options) {
			$groups[] = sprintf($format, $group, $this->getUngroupedOptions($options));
		}
		
		return join("", $groups);
	}
	
	/**
	 * @return string
	 */
	protected function getFieldAsFieldset(): string {
	
		// radio buttons are very much like our selection in construction,
		// though different in the specific HTML format, obviously.  first,
		// we convert our array of options into a list of radio buttons.
		// then, we'll take that list and cram it into the surrounding HTML
		// for our radio button set.
		
		$radios = $this->getInputsAsString();
		$format = '
			<li class="%s">
				<fieldset id="%s">
				<legend>%s</legend>
				%s
				<ol>
					%s
				</ol>
				</fieldset>
			</li>
		';
		
		return sprintf($format,
			$this->getLiClass([$this->extraType]),
			$this->getId(),
			$this->getLabel(),
			$this->getVerboseInstructions(),
			$radios
		);
	}
	
	/**
	 * @return string
	 */
	protected function getInputsAsString(): string {
		
		// similar to our getOptionsAsString method above, we want to confirm
		// that we have options and that they are single-dimensional.  then,
		// we'll build 'em.  unlike the <select> element, we don't care about
		// the depth of our options array because it's got to be one for our
		// buttons.
		
		$this->validateOptions(1);
		
		// if we're executing here, then we didn't run into an exception
		// within the validateOptions() method and we're good to go.
		
		$format = '
			<li class="radio">
				<label>
					<input type="radio" name="%s" value="%s" class="%s"%s>
					<span class="radio-label">%s</span>
				</label>
			</li>
		';
		
		$radios = [];
		foreach ($this->options as $value => $text) {
			$checked = $this->isSelected($value) ? " checked" : "";
			$radios[] = sprintf($format, $this->getName(), $value,
				$this->getClassesAsString(), $checked, $text
			);
		}
		
		return join("", $radios);
	}
	
	/**
	 * @param string $optionOptionValue
	 *
	 * @return bool
	 */
	protected function isSelected(string $optionOptionValue): bool {
		
		// determining if this option is selected (or checked) is an important
		// part of some methods above, but it's also different when we're
		// selecting many options with our children.  so, despite it's
		// simplicity, we separate it into its own method here.
		
		return $optionOptionValue === $this->value ? true : false;
	}
}
