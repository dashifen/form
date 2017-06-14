<?php

namespace Dashifen\Form\Fieldset;

use Dashifen\Form\Fields\FieldInterface;
use Dashifen\Form\Fields\AbstractField;

/**
 * Class Fieldset
 *
 * @package Dashifen\Form\Fieldset
 */
class Fieldset implements FieldsetInterface {
	/**
	 * @var string
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $legend;
	
	/**
	 * @var array
	 */
	protected $fields = [];
	
	/**
	 * @var array
	 */
	protected $classes = [];
	
	/**
	 * @var string
	 */
	protected $instructions = "";
	
	/**
	 * Fieldset constructor.
	 *
	 * @param string $id
	 * @param string $legend
	 */
	public function __construct(string $id, string $legend = "") {
		$this->id = $id;
		$this->legend = empty($legend)
			? ucwords(str_replace("-", " ", $id))
			: $legend;
	}
	
	
	
	/**
	 * @param string $jsonFieldset
	 *
	 * @return FieldsetInterface
	 * @throws FieldsetException
	 */
	public static function parse(string $jsonFieldset): FieldsetInterface {
		$fieldsetData = json_decode($jsonFieldset);
		
		// like the parser for our form, the first thing we do is set a
		// few of our properties.  we'll use the null coalescing operator
		// to make this look slick all on one line.  first, we handle the
		// id and legend, since those are parameters for our constructor.
		
		$id = $fieldsetData->id ?? uniqid("fieldset-");
		$legend = $fieldsetData->legend ?? "";
		$fieldset = new Fieldset($id, $legend);
		
		// now, the classes and instructions are next.
		
		$classes = $fieldsetData->classes ?? [];
		$instructions = $fieldsetData->instructions ?? "";
		$fieldset->setInstructions($instructions);
		
		// for classes, if it's not an array, we're going to
		// assume it's a space-separated string of our classes.
		// we can explode() that and send it on its way.
		
		if (!is_array($classes)) {
			if (is_string($classes)) {
				$classes = explode(" ", $classes);
			} else {
				throw new FieldsetException(
					"Parse error: Fieldset classes must be array or string",
					FieldsetException::INVALID_CLASSES
				);
			}
		}
		
		$fieldset->setClasses($classes);
		
		// finally, we'll want to process our fields one by one and
		// add them to this set.  then, we can return our fieldset
		//
		
		$fields = $fieldsetData->fields ?? [];
		foreach ($fields as $field) {
			if (is_object($field)) {
				$field = json_encode($field);
			}
			
			$fieldset->addField(AbstractField::parse($field));
		}
		
		return $fieldset;
	}
	
	/**
	 * @param string $class
	 */
	public function setClass(string $class): void {
		$this->classes[] = $class;
	}
	
	/**
	 * @param array $classes
	 */
	public function setClasses(array $classes): void {
		
		// we don't want to simply set our property to our argument
		// because that would undo any other work done on it.  instead,
		// we'll merge and then make sure thing are unique.
		
		$temp = array_merge($this->classes, $classes);
		$temp = array_unique(array_filter($temp));
		$this->classes = $temp;
	}
	
	/**
	 * @return string
	 */
	public function getClassesAsString(): string {
		
		// at the time of this comment, the getClasses() method doesn't
		// do anything interesting, but in case it grows up to be a swan,
		// we'll use it here.
		
		return join(" ", $this->getClasses());
	}
	
	/**
	 * @return array
	 */
	public function getClasses(): array {
		return $this->classes;
	}
	
	/**
	 * @param string $instructions
	 */
	public function setInstructions(string $instructions): void {
		$this->instructions = $instructions;
	}
	
	/**
	 * @return string
	 */
	public function getInstructions(): string {
		return $this->instructions;
	}
	
	/**
	 * @param FieldInterface $field
	 */
	public function addField(FieldInterface $field): void {
		$this->fields[$field->getId()] = $field;
	}
	
	/**
	 * @param array $fields
	 *
	 * @return void
	 * @throws FieldsetException
	 */
	public function addFields(array $fields): void {
		
		// given an array of fields, this method makes sure that they're
		// actually fields.  we could send them directly too addField, but
		// this way we can be a little more descriptive with our Exception.
		
		foreach ($fields as $field) {
			if ($field instanceof FieldInterface) {
				$this->addField($field);
			} else {
				throw new FieldsetException(
					"Cannot add non-field to fieldset.",
					FieldsetException::NOT_A_FIELD
				);
			}
		}
	}
	
	/**
	 * @param string $fieldId
	 *
	 * @return bool
	 */
	public function hasField(string $fieldId): bool {
		
		// our $fields array is indexed by field IDs.  so, we can know
		// that this fieldset has this field by seeing if our argument
		// is in the keys of our array:
	
		return array_key_exists($fieldId, $this->fields);
	}
	
	/**
	 * @param string      $fieldId
	 * @param string      $error
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public function addError(string $fieldId, string $error, string $value = null): bool {
		
		// in a perfect world, programmers would always call hasField before
		// this one.  but, since we can't be sure, we're going to call it here,
		// too.  worst case:  we use array_key_exists() twice and that's not
		// so bad.
		
		if (($isFound = $this->hasField($fieldId))) {
			$this->fields[$fieldId]->setError($error, $value);
		}
		
		return $isFound;
	}
	
	/**
	 * @param string $fieldId
	 * @param string $value
	 */
	public function addValue(string $fieldId, string $value): void {
		
		// adding a value is just like adding an error where the error
		// message is empty.  so, we can just call the prior method with
		// our arguments here and let it handle everything.
		
		$this->addError($fieldId, "", $value);
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getFieldset(bool $display = false): string {
		
		// the $display parameter for fieldsets (and, incidentally, for fields) is
		// intentionally the opposite than for forms.  that's because we collect
		// information about fieldsets (and fields) using this method and return
		// them "up" to the form which can then display them.
		
		$format = '
			<fieldset id="%s" class="%s">
			<legend><label for="%s">%s</label></legend>
			%s
			<ol>
				%s
			</ol>
			</fieldset>
		';
		
		$fieldset = sprintf($format,
			$this->id,
			$this->getClassesAsString(),
			$this->id,
			$this->legend,
			$this->getVerboseInstructions(),
			$this->getContents()
		);
		
		if ($display) {
			echo $fieldset;
			$fieldset = "";
		}
		
		return $fieldset;
	}
	
	/**
	 * @return string
	 */
	protected function getVerboseInstructions() {
		
		// our verbose instructions are those that are ready for display
		// as a part of our HTML.  this is different from simply getting
		// the property's value and returning it.  notice that if we don't
		// have instructions for this fieldset, we return an empty string,
		// not an empty paragraph.
		
		return !empty($this->instructions)
			? "<p>" . $this->getInstructions() . "</p>"
			: "";
	}
	
	/**
	 * @return string
	 */
	protected function getContents() {
		$contents = "";
		
		// the content of a fieldset is the concatenation of its fields'
		// content.  so, we'll iterate through our list of fields and let
		// them tell us what to display.
		
		foreach ($this->fields as $field) {
			/** @var FieldInterface $field */
			
			$contents .= $field->getField();
		}
		
		return $contents;
	}
}
