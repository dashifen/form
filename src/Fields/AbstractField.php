<?php

namespace Dashifen\Form\Fields;

abstract class AbstractField implements FieldInterface {
	
	/**
	 * @var array
	 *
	 * to avoid finding the namespace for types over and over again,
	 * we'll use this property to store them.  it's static so that we
	 * can use it in the static getNamespacedType() method.
	 */
	protected static $types = [];
	
	protected $id;
	protected $name;
	protected $type;
	protected $label;
	protected $options = [];
	protected $classes = [];
	protected $required = "";
	protected $instructions = "";
	protected $errorMessage = "";
	protected $additionalAttributes = [];
	protected $validation = [];
	protected $error = false;
	protected $value = "";
	
	// some fields have JSON values; we only want to transform them
	// once, and so we'll store transformed JSON values here when we
	// need to.
	
	protected $transformedJsonValue = null;
	
	// sometimes, some fields may need to restrict the ability to make
	// changes to a its own properties.  in such a case, it sets the
	// locked flag.
	
	protected $locked = false;
	
	// most fields have an expected field count of 1.  that is, there's
	// 1 element in the DOM that has a value that makes up this field.
	// but, others need more than that so they can change this property.
	
	protected $productFieldCount = 1;
	
	public function __construct(string $id, string $name = "", string $label = "") {
		
		// if our name is empty, we'll make it match our id.  then, we can
		// use that name to create a label if one was not specified.  thus,
		// a field can have an ID of first-name (for example), gets the
		// matching name, and its label becomes "First Name" after the
		// transformation below.
		
		if (empty($name)) {
			$name = $id;
		}
		
		$this->label = empty($label)
			? ucwords(str_replace("-", " ", $name))
			: $label;
		
		$this->name = $name;
		$this->id = $id;
		
		// for our type property, we'll default to the name of the called
		// class.  this will allow children to avoid the need for their own
		// constructors unless they have to do something other than specify
		// their type.
		
		$this->setType();
	}
	
	/**
	 * @param string $jsonField
	 *
	 * @return FieldInterface
	 * @throws FieldException
	 */
	public static function parse(string $jsonField): FieldInterface {
		$fieldData = json_decode($jsonField);
		
		// echo "<pre>" . print_r($fieldData, true) . "</pre>";
		
		// this parse method is much like the one for the Form and Fieldset.
		// but, it has to parse many different types of fields.  normally, a
		// child could override it, but the Fieldset's parse method explicitly
		// calls this one at the moment.  so, we try to keep it as general as
		// possible.
		
		// first, we grab the properties that we want to mess with here.
		// some are parameters for the constructor; others we send in with
		// setters after it's been constructed.  notice that these first
		// three are interdependent;  their order is important.
		
		$id = $fieldData->id ?? uniqid("field-");
		$name = $fieldData->name ?? $id;
		$label = $fieldData->label ?? ucwords(str_replace("-", " ", $name));
		$type = $fieldData->type ?? "Text";
		
		// we use a variable constructor because this object is abstract.
		// therefore, we need to call the constructor for the type of field
		// that we're instantiating here.  if you look above, you'll see
		// that we default to a text field if one is not specified.  but,
		// since we won't have use statements for our fields, we need to
		// get their fully qualified namespace unless $type already has it.
		
		$namespaced_type = strpos($type, '\\') === false
			? AbstractField::getNamespacedType($type)
			: $type;
		
		/** @var FieldInterface $field */
		
		$field = new $namespaced_type($id, $name, $label);
		
		// here's where the $locked flag comes into play.  if a field needs to
		// lock itself after construction, then our isLocked() method will
		// return true and we're done.
		
		if (!$field->isLocked()) {
			
			// first we handle our simply data types as follows.  these are
			// simply strings, Booleans, etc. so the null coalescing operator
			// is good at setting these up.
			
			$field->setInstructions($fieldData->instructions ?? "");
			$field->setRequired($fieldData->required ?? self::OPTIONAL);
			
			// now we need to mess with the more complex stuff.  we'll
			// create an anonymous function here that we use for a few
			// properties.  we use an anonymous function because this
			// method is static.
			
			$transformProperty = function($property): array {
				
				// our $property parameter can be one of three things: an
				// array, object, or string.  if it's a string, we could
				// have either a space separated string or JSON.  if it's
				// an array, then we're already done:
				
				if (is_array($property)) {
					return $property;
				}
				
				// objects are easy, too:
				
				if (is_object($property)) {
					return (array)$property;
				}
				
				// and, for strings, we assume it's JSON until proven
				// otherwise.  then, we assume it's a space-separated list.
				
				$temp = json_decode($property, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					return $temp;
				}
				
				return explode(" ", $property);
			};
			
			$field->setAdditionalAttributes($transformProperty($fieldData->additionalAttributes ?? []));
			$field->setClasses($transformProperty($fieldData->classes ?? []));
			$field->setOptions($transformProperty($fieldData->options ?? []));
		}
		
		// even if the field was locked, error message and values should
		// still be set.  most of the time, our values come to us as strings,
		// or things that can be cast as strings, but if it's an array, we'll
		// need to do it ourselves.
		
		$value = $fieldData->value ?? "";
		
		if (!is_string($value)) {
			
			// we don't want to join() our $value in case the separator that
			// we choose is actually a part of it.  instead, we'll just encode
			// it as a JSON string and assume the field object which receives
			// it knows how to proceed.
			
			$value = json_encode($value);
		}
		
		$field->setError($fieldData->errorMessage ?? "");
		$field->setValue($value);
		return $field;
	}
	
	/**
	 * @param string $type
	 *
	 * @return string
	 * @throws FieldException
	 */
	public static function getNamespacedType(string $type): string {
		if (isset(static::$types[$type])) {
			return static::$types[$type];
		}
		
		// given our un-namespaced $type, we want to look for it
		// within our Elements folder.  when we find it, we then
		// need to construct the namespaced version of that type
		// and return it.  we expect that this is mostly useful for
		// the parse() method above, but maybe it'll be useful in
		// other places, too.
		
		$dir = new \RecursiveDirectoryIterator(__DIR__);
		$files = new \RecursiveIteratorIterator($dir);
		foreach ($files as $file) {
			/** @var \SplFileInfo $file */
			
			$basename = $file->getBasename(".php");
			if ($basename === $type) {
				
				// now that we've found the file that defines our
				// $type, we need to determine its namespace.  we
				// could open the file and actually read it out of
				// the file's content, but that's expensive.  the
				// other way to go is to build it based on the path
				// to the file itself.
				
				$path = $file->getPath();
				$path = str_replace(DIRECTORY_SEPARATOR, "\\", $path);
				$middle = substr($path, strpos($path, 'src\\') + 4);
				$namespace = '\Dashifen\Form\\' . $middle . '\\' . $type;
				
				// so that we don't have to do all of this work over
				// again just to find the same information for this $type,
				// we'll store it in our static property.
				
				static::$types[$type] = $namespace;
				return $namespace;
			}
		}
		
		// if we didn't return within the above loop, then we never
		// found a field of this $type.  all we can do is throw this
		// exception and hope that this problem can be solved else-
		// where.
		
		throw new FieldException("Unknown field: $type",
			FieldException::UNKNOWN_FIELD);
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	abstract public function getField(bool $display = false): string;
	
	/**
	 * @param string $id
	 *
	 * @return bool
	 */
	public function is(string $id): bool {
		
		// this function checks to see if this field "is" described
		// but the $id argument.  e.g. $this->is("first-name").  so,
		// all we need to do is check for equality between our ID
		// property and the argument.
		
		return $this->id === $id;
	}
	
	/**
	 * @return bool
	 */
	public function isLocked(): bool {
		return $this->locked;
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty(): bool {
		return empty($this->value);
	}
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getId(string $suffix = ""): string {
		
		// some fields use simply their ID in the DOM to identify a field.
		// but others, especially those which may have multiple elements to
		// create a single Field, may use suffixes to differentiate between
		// elements.  when $suffix is not empty, we append it to our ID
		// property; otherwise, we just return our property.
		
		return !empty($suffix)
			? sprintf("%s-%s", $this->id, $suffix)
			: $this->id;
	}
	
	/**
	 * @param string $id
	 */
	public function setId(string $id): void {
		$this->id = $id;
	}
	
	/**
	 * @param string $name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getName(string $suffix = ""): string {
		
		// some fields use simply their name in the DOM to identify a field.
		// but others, especially those which may have multiple elements to
		// create a single Field, may use suffixes to differentiate between
		// elements.  when $suffix is not empty, we append it to our name
		// property; otherwise, we just return our property.
		
		return !empty($suffix)
			? sprintf("%s-%s", $this->name, $suffix)
			: $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}
	
	public function setType(string $type = ""): void {
		if (empty($type)) {
			
			// if $type is empty, then we want to make sure that we
			// turn it into the lower case name of this class.  because
			// we want the name of the classes which extend this one, we
			// use static::class rather than self::class.  the latter
			// would always be AbstractField, after all.
			
			$temp = explode("\\", static::class);
			$type = strtolower(array_pop($temp));
		}
		
		$this->type = $type;
	}
	
	/**
	 * @param string $class
	 */
	public function setClass(string $class): void {
		$this->classes[] = $class;
	}
	
	public function getClassesAsString(): string {
		
		// instead of returning the array, this one returns a string
		// that can be crammed right into the value for an HTML
		// attribute.
		
		return join(" ", $this->classes);
	}
	
	/**
	 * @return array
	 */
	public function getClasses(): array {
		return $this->classes;
	}
	
	/**
	 * @param array $classes
	 */
	public function setClasses(array $classes): void {
		
		// we don't want to set our property to the argument because
		// that might undo work done elsewhere.  instead, we merge and
		// then ensure that we have a unique list as follows.
		
		$temp = array_merge($classes, $this->classes);
		$temp = array_filter(array_unique($temp));
		$this->classes = $temp;
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
	 * @return bool
	 */
	public function getRequired(): bool {
		return $this->required;
	}
	
	/**
	 * @param bool $required
	 */
	public function setRequired(bool $required): void {
		$this->required = $required;
	}
	
	/**
	 * @return string
	 */
	public function getValue(): string {
		return $this->value;
	}
	
	/**
	 * @param string $value
	 */
	public function setValue(string $value): void {
		$this->value = $value;
	}
	
	public function getOptions(): array {
		return $this->options;
	}
	
	public function setOptions(array $options): void {
		
		// unlike setting classes, we just want someone to send in all
		// possible options at once here.  we'll simply replace our
		// property with the argument as there's no need to merge them.
		
		$this->options = $options;
	}
	
	/**
	 * @return array
	 */
	public function getAdditionalAttributes(): array {
		return $this->additionalAttributes;
	}
	
	/**
	 * @param array $additionalAttributes
	 */
	public function setAdditionalAttributes(array $additionalAttributes): void {
		$this->additionalAttributes = $additionalAttributes;
	}
	
	/**
	 * @param array $validation
	 */
	public function setValidation(array $validation): void {
		$this->validation = $validation;
	}
	
	/**
	 * @return array
	 */
	public function getValidation(): array {
		return $this->validation;
	}
	
	/**
	 * @param string|null $value
	 */
	public function resetError(string $value = null): void {
		
		// resetting our error message is as simply as sending an empty
		// string to the prior method along with our optional value.
		
		$this->setError("", $value);
	}
	
	/**
	 * @return string
	 */
	public function getError(): string {
		return $this->errorMessage;
	}
	
	/**
	 * @param string      $error
	 * @param string|null $value
	 */
	public function setError(string $error, string $value = null): void {
		
		// when setting an error, we want to save the error message that
		// was passed here as well as set our error flag.  both are used
		// in the getLabel() method below.
		
		$this->errorMessage = $error;
		$this->error = !empty($error);
		
		// if a value was sent here, too, we'll set that, too.  this is
		// mostly for our convenience.
		
		if (!is_null($value)) {
			$this->setValue($value);
		}
	}
	
	/**
	 * @return int
	 */
	public function getProductFieldCount(): int {
		return $this->productFieldCount;
	}
	
	/**
	 * @param array $potentials
	 *
	 * @return string
	 */
	protected function getAttributesAsString(array $potentials): string {
		$attributes = [];
		
		// the additional attributes - our potentials - that a Number
		// field cares about are the step, min, and max attributes.  we
		// loop over those and check for them in our additionalAttributes
		// property.
		
		foreach ($potentials as $potential) {
			if (isset($this->additionalAttributes[$potential])) {
				$potentialValue = $this->additionalAttributes[$potential];
				$attributes[] = sprintf('%s="%s"', $potential, $potentialValue);
			}
		}
		
		// by joining our attributes together, we'll get something that may
		// look like this 'step="1" min="0" max="10"' which we return to the
		// calling scope.
		
		return join(" ", $attributes);
	}
	
	/*
	 * The following protected methods are all called in some way by our
	 * children's getField() methods.  they're placed here because it's
	 * often the case that different fields can use them (or override
	 * them) even though each field is, in the end, different.
	 */
	
	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	protected function getLiClass(array $classes = []): string {
		
		// if any classes were passed here by a child, then we leave
		// them alone and add the following.  in case a child mistakenly
		// adds any of these, we'll de-duplicate our array before we
		// merge them into the appropriate HTML format for the class
		// attribute.
		
		$classes[] = "field";
		$classes[] = "field-" . $this->type;
		$classes[] = $this->id;
		
		return join(" ", array_filter(array_unique($classes)));
	}
	
	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	protected function getLabel(array $classes = []): string {
		
		// the purpose of this function is to return an HTML <label> tag
		// as a string.  to do that, we use a few of our properties to
		// control the content of the label and it's attributes.  first,
		// we want to build our classes in a similar capacity to the way
		// we did it for the get_le_class() method above.
		
		$classes[] = $this->required ? "required" : "optional";
		$classes[] = $this->error === false ? "no-error" : "error";
		$classes[] = $this->type;
		$classes[] = $this->name;
		$classes[] = $this->id;
		
		$class = join(" ", array_filter(array_unique($classes)));
		
		// now, to make things easier on ourselves, we'll use output
		// buffering to build the HTML.  otherwise, we need a lot of
		// very careful work with sprintf() to concatenate all of this.
		
		ob_start(); ?>
		
		<label for="<?= $this->id ?>" class="<?= $class ?>">
			<span><?= $this->label ?></span>
			
			<?php if ($this->required) { ?>
				<i class="fa fa-star" aria-hidden="true" title="required"></i>
			<?php }
			
			if ($this->error !== false) { ?>
				<strong role="alert"><?= $this->errorMessage ?></strong>
			<?php } ?>
		
		</label>
		
		<?php return ob_get_clean();
	}
	
	protected function getVerboseInstructions(): string {
		
		// the verbose instructions are prepared for immediate use in an
		// HTML string.  notice that if we don't have any instructions for
		// this field, we return an empty string and not an empty paragraph.
		
		return !empty($this->instructions)
			? "<p>" . $this->instructions . "</p>"
			: "";
	}
	
	/**
	 * @param array $default
	 *
	 * @return array
	 * @throws FieldException
	 */
	protected function transformJsonValue(array $default = []): array {
		if ($this->isEmpty()) {
			return $default;
		}
		
		// if we've already transformed our value, then it'll be in this
		// property.  to avoid consistently transforming and re-transforming
		// the value, we'll return our prior results if we have them.
		
		if (!is_null($this->transformedJsonValue)) {
			return $this->transformedJsonValue;
		}
		
		// now, we'll do our JSON transformation.  if we don't run into errors,
		// we assume we're good to go until proven otherwise.  otherwise, we
		// throw our exception and hope it's caught elsewhere.
		
		$values = json_decode($this->value, true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$message = sprintf("%s requires JSON value", $this->getType());
			throw new FieldException($message, FieldException::INVALID_VALUE);
		}
		
		// here's where we save our transformation for later.  we'll assign
		// our $values to our property and then return those values by side
		// effect as well.
		
		return ($this->transformedJsonValue = $values);
	}
	
	/**
	 * @param string $field
	 * @param bool   $display
	 *
	 * @return string
	 */
	protected function display(string $field, bool $display = false): string {
		
		// this method is used by children to either echo or return their
		// HTML representation.  this helps in case a child needs to change
		// the way they display their field for some reason.  since we
		// normally make strings of our fields and pass their display up to
		// the sets which, in turn, pass their display to our form, we assume
		// that we don't want to echo our fields here.
		
		if ($display) {
			echo $field;
			$field = "";
		}
		
		return $field;
	}
}
