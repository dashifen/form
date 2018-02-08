<?php

namespace Dashifen\Form;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\FieldException;
use Dashifen\Form\Fields\FieldInterface;
use Dashifen\Form\Fieldset\Fieldset;
use Dashifen\Form\Fieldset\FieldsetException;
use Dashifen\Form\Fieldset\FieldsetInterface;

/**
 * Class Form
 *
 * @package Dashifen\Form
 */
class Form implements FormInterface {
	public const ENCTYPES = [
		"application/x-www-form-urlencoded",
		"multipart/form-data",
		"text/plain",
	];
	
	public const ENCTYPE_DEFAULT = self::ENCTYPES[0];
	public const ENCTYPE_URLENCODED = self::ENCTYPES[0];
	public const ENCTYPE_MULTIPART = self::ENCTYPES[1];
	public const ENCTYPE_TEXT = self::ENCTYPES[2];
	
	/**
	 * @var string
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $action;
	
	/**
	 * @var string
	 */
	protected $enctype = self::ENCTYPE_DEFAULT;
	
	/**
	 * @var string
	 */
	protected $method = "post";
	
	/**
	 * @var array
	 */
	protected $buttons = [];
	
	/**
	 * @var string
	 */
	protected $instructions = "";
	
	/**
	 * @var array
	 */
	protected $classes = [];
	
	/**
	 * @var array
	 */
	protected $fieldsets = [];
	
	/**
	 * @var bool
	 */
	protected $error = false;
	
	/**
	 * Form constructor.
	 *
	 * @param string $id
	 */
	public function __construct(string $id) {
		$this->id = $id;
	}
	
	/**
	 * @param string $jsonForm
	 *
	 * @return FormInterface
	 * @throws FormException
	 */
	public static function parse(string $jsonForm): FormInterface {
		$formData = json_decode($jsonForm);
		
		// to construct our form, we need an id for it.  if one was not
		// specified, we'll create one using unique() as follows.
		
		$form = new Form($formData->id ?? uniqid("form-"));
		
		// next, we'll handle the form attributes based on their existence
		// in the JSON data or their defaults.  we could use if-statements
		// rather than the null coalescing operator, but this looks cleaner
		// than all the curly braces and blank space, IMO.  and, these
		// methods don't have that much overhead that calling them during
		// every parse causes that much of a delay.
		
		$form->setAction($formData->action ?? "");
		$form->setMethod($formData->method ?? "post");
		$form->setEnctype($formData->enctype ?? "application/x-www-form-urlencoded");
		$form->setInstructions($formData->instructions ?? "");
		
		// a form's classes should be an array, but we might get them as a
		// string.  if we do, we assume is a JSON string that we can decode
		// to form the expected array.
		
		$classes = $formData->classes ?? "[]";
		if (!is_array($classes)) {
			$classes = json_decode($classes, true);
		}
		
		$form->setClasses($classes);
		
		// now, for each of the fieldsets described within our form data
		// object, we want to create a Fieldset object with its parse method
		// and then add that object to the form we're creating.
		
		$fieldsets = $formData->fieldsets ?? [];
		foreach ($fieldsets as $fieldset) {
			if (is_object($fieldset)) {
				$fieldset = json_encode($fieldset);
			}
			
			try {
				$form->addFieldset(Fieldset::parse($fieldset));
			} catch (FieldsetException $exception) {
				throw new FormException("Must add a fieldset.",
					FormException::NOT_A_FIELDSET,
					$exception);
			}
			
		}
		
		// finally, if buttons are specified as a part of our form
		// data, then we'll add them now.  buttons are optional, i.e.
		// the form will add a submit button for us if we don't have
		// other buttons to show when we display it.
		
		$buttons = $formData->buttons ?? [];
		foreach ($buttons as $button) {
			try {
				$form->addButton(AbstractField::parse($button));
			} catch (FieldException $exception) {
				throw new FormException("Must add a button",
					FormException::NOT_A_BUTTON,
					$exception);
			}
		}
		
		return $form;
	}
	
	/**
	 * @param FieldsetInterface $fieldset
	 */
	public function addFieldset(FieldsetInterface $fieldset): void {
		$this->fieldsets[] = $fieldset;
	}
	
	/**
	 * @param FieldInterface $button
	 *
	 * @throws FormException
	 */
	public function addButton(FieldInterface $button): void {
		
		// if we're here, then we know $button is a FieldInterface
		// object, but we want to be sure that it's a button.  we can
		// do that by checking its type.
		
		if (stripos($button->getType(), "button") !== false) {
			$this->buttons[] = $button;
			return;
		}
		
		throw new FormException("Cannot add a non-button with button adder.",
			FormException::NOT_A_BUTTON);
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}
	
	/**
	 * @param string $method
	 */
	public function setMethod(string $method): void {
		
		// html forms can only use get and post methods.  so if
		// method is anything other than "get" we default to post,
		// since that's more common.
		
		$this->method = strtolower($method) === "get" ? "get" : "post";
	}
	
	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}
	
	/**
	 * @param string $action
	 */
	public function setAction(string $action): void {
		
		// an action can be empty -- then the form just submits to the
		// same URL on which it is displayed.  thus,
		
		$this->action = !empty($action) ? $action : "";
	}
	
	/**
	 * @return string
	 */
	public function getEnctype(): string {
		return $this->enctype;
	}
	
	/**
	 * @param string $enctype
	 */
	public function setEnctype(string $enctype): void {
		
		// the above are the valid enctypes for an HTML form.  if we
		// don't have one of those, we'll just use the default option.
		
		if (!in_array($enctype, self::ENCTYPES)) {
			$enctype = self::ENCTYPE_DEFAULT;
		}
		
		$this->enctype = $enctype;
	}
	
	/**
	 * @return string
	 */
	public function getInstructions(): string {
		return $this->instructions;
	}
	
	/**
	 * @param string $instructions
	 */
	public function setInstructions(string $instructions): void {
		$this->instructions = $instructions;
	}
	
	/**
	 * @return array
	 */
	public function getClasses(): array {
		return $this->classes;
	}
	
	/**
	 * @param array $classes
	 *
	 * @return void
	 */
	public function setClasses(array $classes) {
		$this->classes = $classes;
	}
	
	/**
	 * @param array $fieldsets
	 *
	 * @throws FormException
	 */
	public function addFieldsets(array $fieldsets): void {
		
		// sometimes it's handy to send a bunch of fieldsets all at
		// once.  the only thing we need to do here is be sure that
		// each member of our array implements FieldsetInterface and
		// then we call the next method.
		
		foreach ($fieldsets as $fieldset) {
			if ($fieldset instanceof FieldsetInterface) {
				$this->addFieldset($fieldset);
			} else {
				throw new FormException(
					"Cannot add non-fieldset to form.",
					FormException::NOT_A_FIELDSET
				);
			}
		}
	}
	
	/**
	 * @return array
	 */
	public function getFieldsets(): array {
		return $this->fieldsets;
	}
	
	/**
	 * @param string $field
	 * @param string $value
	 */
	public function addFieldValue(string $field, string $value): void {
		
		// the adding of values and errors is very similar.  in fact, we're
		// just going to pass control to the addError method below because
		// we can use our arguments and a blank error message to do there to
		// do what we would otherwise do here.
		
		$this->addFieldError($field, "", $value);
	}
	
	/**
	 * @param string      $field
	 * @param string      $error
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public function addFieldError(string $field, string $error, string $value = null): bool {
		
		// to add an error on a field in this form, we have to find the
		// fieldset which contains that field.  so we'll loop over our
		// fieldsets and break when we find the right one.
		
		foreach ($this->fieldsets as $fieldset) {
			/** @var FieldsetInterface $fieldset */
			
			if ($fieldset->hasField($field)) {
				$fieldset->addError($field, $error, $value);
				return true;
			}
		}
		
		// if we made it out of the loop above, then we never found this
		// field anywhere within our form.  so, we'll return false and the
		// calling scope can do something about that.
		
		return false;
	}
	
	public function resetError(string $instructions): void {
		
		// at the moment, the prior method doesn't do anything that special.
		// but, in case we one day want to do something with our instructions
		// before setting them, for example, we'll pass control back to it
		// and specify the state of our error flag as false;
		
		$this->setError($instructions, false);
	}
	
	/**
	 * @param string $instructions
	 * @param bool   $state
	 */
	public function setError(string $instructions, bool $state = true): void {
		
		// we set an error with instructions because we assume that those
		// instructions will help a person to fix the problem we found in
		// the form.
		
		$this->setInstructions($instructions);
		$this->error = $state;
	}
	
	/**
	 * @return array
	 */
	public function getFields(): array {
		
		// getting our form's fields is a little complicated because they're
		// actually collected by our fieldsets.  so, we'll loop over those and
		// get their fields, merging them all together into one giant field
		// list.
		
		$fields = [];
		foreach ($this->fieldsets as $fieldset) {
			$fields = array_merge($fields, $fieldset->getFields());
		}
		
		return $fields;
	}
	
	/**
	 * @param string $id
	 *
	 * @return FieldInterface|null
	 */
	public function getField(string $id): ?FieldInterface {
		
		// for a form to get a specific field requires that we look for the
		// fieldset in which that field has been added.  so, we loop over the
		// sets, and when we find our field, we return it.
		
		foreach ($this->fieldsets as $fieldset) {
			if ($fieldset->hasField($id)) {
				return $fieldset->getField($id);
			}
		}
		
		return null;
	}
	
	/**
	 * @param array $buttons
	 *
	 * @throws FormException
	 */
	public function addButtons(array $buttons): void {
		
		// like adding our fieldsets above, this method checks to be sure
		// that when we're adding buttons to our form that they are, indeed,
		// Button objects.  unlike that one, the best we can do here is
		// ensure that they're FieldInterface objects.  addButton() below
		// will do more.
		
		foreach ($buttons as $button) {
			if ($button instanceof FieldInterface) {
				$this->addButton($button);
			} else {
				throw new FormException(
					"Cannot add a non-button to form.",
					FormException::NOT_A_BUTTON
				);
			}
		}
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 * @throws FormException
	 */
	public function getForm(bool $display = true): string {
		
		// the purpose of this method is to build the actual HTML string
		// that becomes our form.  first, we'll identify the attributes for
		// our <form> tag.  we always use the id and method.  but, then we
		// might also use action and enctype.
		
		$attributes = ["id", "method", "class"];
		
		if (!is_null($this->action) && !empty($this->action)) {
			$attributes[] = "action";
		}
		
		if ($this->method === "post") {
			$attributes[] = "enctype";
			
			// if we're posting data, we might be posting a file.  in
			// such a case, we must have the multiple/form-data enctype
			// or the file doesn't get sent.  so, if we don't have that
			// enctype and we do have a file input, then we'll set the
			// appropriate enctype here.
			
			if ($this->enctype !== self::ENCTYPE_MULTIPART && $this->hasFieldOfType("file")) {
				$this->setEnctype(self::ENCTYPE_MULTIPART);
			}
		}
		
		// now, we'll build our <form> using the $attributes array we
		// just created above.  most of our attributes can be accessed
		// directly out of their properties, but the "class" attribute
		// has to be constructed from the classes one using join().
		
		$form = "<form";
		
		foreach ($attributes as $attribute) {
			$attributeValue = $attribute === "class"
				? join(" ", $this->classes)
				: $this->{$attribute};
			
			if (strlen($attributeValue) !== 0) {
				$form .= " $attribute=$attributeValue";
			}
		}
		
		$form .= ">";
		
		// next, we use other methods of this object to add the content of
		// our form before closing it up.
		
		$form .= $this->getVerboseInstructions();
		$form .= $this->getContents();
		$form .= $this->getButtons();
		$form .= "</form>";
		
		// finally, if we're displaying the form, we echo it to the client
		// and then empty it.  this way, when we return below, we won't
		// display and return it all at once.
		
		if ($display) {
			echo $form;
			$form = "";
		}
		
		return $form;
	}
	
	public function hasFieldOfType(string $type): bool {
		
		// like the prior method, we loop over our fieldsets and see if any
		// of them have a field of this type.  if one does, we return true;
		// otherwise, false.
		
		foreach ($this->fieldsets as $fieldset) {
			if ($fieldset->hasFieldOfType($type)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @return string
	 */
	protected function getVerboseInstructions(): string {
		
		// the verbose instructions are those which appear as a part of
		// the HTML form and not simply as the string of instructions
		// stored in our property.
		
		$instructions = '<div class="%s">%s</div>';
		$classes[] = "instructions";
		$content = "";
		
		if (!empty($this->instructions)) {
			if ($this->error) {
				$classes[] = "notice notice-error";
			}
			
			$content = "<p>" . $this->instructions . "</p>";
		}
		
		return sprintf($instructions, join(" ", $classes), $content);
	}
	
	/**
	 * @return string
	 */
	protected function getContents() {
		$content = "";
		
		// the content of our form is made up of the content of its
		// fieldsets.  so, we'll call their method to get their content.
		// in turn, they'll call their fields' methods.
		
		foreach ($this->fieldsets as $fieldset) {
			/** @var FieldsetInterface $fieldset */
			
			$content .= $fieldset->getFieldset();
		}
		
		return $content;
	}
	
	/**
	 * @return string
	 * @throws FormException
	 */
	protected function getButtons() {
		$buttons = "";
		
		foreach ($this->buttons as $button) {
			/** @var FieldInterface $button */
			
			$buttons .= $button->getField();
		}
		
		if (empty($buttons)) {
			$buttons = $this->getDefaultButton();
		}
		
		return $buttons;
	}
	
	/**
	 * @throws FormException
	 */
	protected function getDefaultButton() {
		
		// this method simply creates a submit button and returns its
		// HTML.  it's used as a way to ensure that each Form has at
		// least this button and it's convenient to not always have to
		// add it manually elsewhere.
		
		try {
			$submit = json_encode([
				"type"  => "SubmitButton",
				"label" => "Submit",
			]);
			
			$submit = AbstractField::parse($submit);
			return $submit->getField();
		} catch (FieldException $exception) {
			throw new FormException("Must add a button.",
				FormException::NOT_A_BUTTON,
				$exception);
		}
	}
	
	public function hasField(string $id): bool {
		
		// a form as a field if one of its fieldsets has it.  we'll loop over
		// our fieldsets and check to see if we can find a field with this $id
		// in any of them.  if we do, then we'll return true.
		
		foreach ($this->fieldsets as $fieldset) {
			if ($fieldset->hasField($id)) {
				return true;
			}
		}
		
		// if we didn't find the field, then we end up here and return false.
		
		return false;
	}
}
