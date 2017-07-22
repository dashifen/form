<?php

namespace Dashifen\Form\Builder;

use Dashifen\Form\Fields\FieldInterface;

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
	 * @param array $description
	 */
	public function __construct(array $description = []) {
		$this->form = [
			"id"           => $description["id"] ?? uniqid("form-"),
			"action"       => $description["action"] ?? "",
			"method"       => $description["method"] ?? "post",
			"enctype"      => $description["enctype"] ?? "application/x-www-form-urlencoded",
			"instructions" => $description["instructions"] ?? "",
			"classes"      => $description["classes"] ?? "[]",
			"fieldsets"    => [],
		];
	}
	
	/**
	 * @param array $description
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function openFieldset(array $description): void {
		
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
		
		$this->currentFieldset++;
		$this->form["fieldsets"][] = [
			"legend"       => $description["legend"],
			"id"           => $description["id"] ?? uniqid("fieldset-"),
			"classes"      => $description["classes"] ?? "",
			"instructions" => $description["instructions"] ?? "",
			"fields"       => [],
		];
	}
	
	/**
	 * @param array $description
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function addField(array $description): void {
		
		// fields work just like the other ones above, and like Fieldsets,
		// there's a requirement to check for.
		
		if (!isset($description["type"])) {
			throw new FormBuilderException("Fields require a type.",
				FormBuilderException::MISSING_FIELD_TYPE);
		}
		
		// and, now we'll handle the normal field setup process like we
		// did for the Form and Fieldset above.  the only hitch is that we
		// want to respect the relationship between the ID, name, and label
		// as defined in the AbstractField::parse() method.  thus, we don't
		// add the latter two unless they exist which means we can't use
		// the super fancy null coalescing operator on them.  so, we leave
		// them to the end.
		
		$field = [
			"type"                 => $description["type"],
			"id"                   => $description["id"] ?? uniqid("field-"),
			"options"              => $description["options"] ?? [],
			"classes"              => $description["classes"] ?? [],
			"required"             => $description["required"] ?? FieldInterface::OPTIONAL,
			"instructions"         => $description["instructions"] ?? "",
			"errorMessage"         => $description["errorMessage"] ?? "",
			"additionalAttributes" => $description["additionalAttributes"] ?? [],
			"error"                => (bool)($description["error"] ?? false),
			"value"                => $description["value"] ?? "",
		];
		
		if (isset($description["name"])) {
			$field["name"] = $description["name"];
		}
		
		if (isset($description["label"])) {
			$field["label"] = $description["label"];
		}
		
		$this->form["fieldsets"][$this->currentFieldset]["fields"][] = $field;
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
