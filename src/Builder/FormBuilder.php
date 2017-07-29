<?php

namespace Dashifen\Form\Builder;

/**
 * Class FormBuilder
 *
 * This object isn't quite a factory.  A factory returns actual objects
 * but this one simply returns a JSON string that we use in conjunction
 * with the Form's parse() method.  The methods below assist in the
 * building of that JSON string.
 *
 * @package Dashifen\Form\Builder
 */
class FormBuilder implements FormBuilderInterface {
	/**
	 * @var array
	 */
	protected $form = [];
	
	/**
	 * @var int
	 */
	protected $currentFieldset = -1;
	
	/**
	 * FormBuilder constructor.
	 *
	 * @param array  $description
	 * @param string $object
	 */
	public function __construct(array $description = [], string $object = 'Dashifen\Form\Form') {
		$this->form = $this->buildObjectArray($object, $description);
		
		// for our form, the one thing we don't expect to be told about at
		// this time is our fieldsets.   if we are, that's fine, but if we're
		// not, then we'll add space for them now.
		
		if (!isset($this->form["fieldsets"])) {
			$this->form["fieldsets"] = [];
		}
	}
	
	/**
	 * @param string $object
	 * @param array  $description
	 *
	 * @return array
	 */
	protected function buildObjectArray(string $object, array $description): array {
		
		// throughout this function, we're giving a description of an object
		// as an array.  here, we use the name of the object being build as
		// well as that description to be sure that we build our form
		// correctly.  with the name of the object, we can use the
		// property_exists() function to copy only that which we need and
		// nothing else.
		
		$objectArray = [];
		foreach ($description as $index => $value) {
			if (property_exists($object, $index)) {
				$objectArray[$index] = $value;
			}
		}
		
		return $objectArray;
	}
	
	/**
	 * @param array  $description
	 * @param string $object
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function openFieldset(array $description = [], string $object = 'Dashifen\Form\Fieldset\Fieldset'): void {
		
		// we're going to impose a requirement here on our fieldsets:  that
		// they have legends.  technically, the Fieldset::parse() method will
		// default to an empty legend if it doesn't get one, but we want to
		// be a little more conscious of the accessibility benefits that a
		// legend provides.
		
		if (!isset($description["legend"])) {
			throw new FormBuilderException("Fieldsets require legends",
				FormBuilderException::MISSING_LEGEND);
		}
		
		// when we "open" a new fieldset, we're going to be adding a new
		// array to the $this->form["fieldsets"] array.  we'll want a way
		// to quickly jump to the current fieldset in that array, which
		// is why we have the currentFieldset property.  it starts at -1
		// so that when we increment it here, our first fieldset will be
		// at 0 as we would expect.
		
		$this->form["fieldsets"][++$this->currentFieldset] = $this->buildObjectArray($object, $description);
		
		// like a form's fieldsets, the fieldset's fields are not expected
		// to be sent here as a part of the $description.  if they are, that's
		// fine, but if they're not, we'll add space for them now.
		
		if (!isset($this->form["fieldsets"][$this->currentFieldset]["fields"])) {
			$this->form["fieldsets"][$this->currentFieldset]["fields"] = [];
		}
	}
	
	/**
	 * @param array  $description
	 * @param string $object
	 *
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function addField(array $description = [], string $object = 'Dashifen\Form\Fields\AbstractField'): void {
		
		// fields work just like the other ones above, and like Fieldsets,
		// there's a requirement to check for.
		
		if (!isset($description["type"])) {
			throw new FormBuilderException("Fields require a type.",
				FormBuilderException::MISSING_FIELD_TYPE);
		}
		
		$this->form["fieldsets"][$this->currentFieldset]["fields"][] = $this->buildObjectArray($object, $description);
	}
	
	/**
	 * @return string
	 * @throws FormBuilderException
	 */
	public function build(): string {
		
		// the work we performed above ensures that our form property
		// is ready-to-go as our JSON string.  so, we can just encode
		// it and return!
		
		return json_encode($this->form);
	}
}
