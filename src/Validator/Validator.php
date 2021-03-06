<?php

namespace Dashifen\Form\Validator;

use Mimey\MimeTypes;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

/**
 * Class AbstractValidator
 * @package Dashifen\Form\Validator
 */
class Validator implements ValidatorInterface {

	/**
	 * @var array
	 */
	protected $validations = [];

	/**
	 * @var array
	 */
	static protected $staticValidations = [];

	/**
	 * Validator constructor.
	 * @throws ReflectionException
	 */
	public function __construct() {

		// to get a list of the available validators, we're going to get the
		// protected methods of this class that return bool and store them in
		// our property.  then, we can use that property below to confirm that
		// any requested validations exist herein.  notice that we only care
		// about protected functions that return a bool.  those are the ones
		// that perform validation operations.

		$reflection = new ReflectionClass($this);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PROTECTED);

		foreach ($methods as $method) {
			if ($method->getReturnType()->getName() === "bool") {
				$this->validations[] = $method->getName();
			}
		}

		// the first time that we do this work, we also want to set our
		// static validations.  that way, when people are working with this
		// object to get rule sets, these will already be available to that
		// static method.

		if (sizeof(static::$staticValidations) === 0) {
			static::$staticValidations = $this->validations;
		}
	}

	/**
	 * @return array
	 */
	public function getValidations(): array {
		return $this->validations;
	}

	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	public function validateAll($value, array $functions): bool {
		if (sizeof($functions) === 0) {
			throw new ValidatorException(
				"Cannot validate all without functions.",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}

		// this method requires that $value pass all of the validation
		// $functions.  so, we'll loop over the array and if we find a
		// failure we can return false.  if we make it all the way through,
		// we return true.

		foreach ($functions as $function) {
			$parameters = [];

			if (is_array($function)) {

				// in order to facilitate the use of this method and
				// validation tests that require a parameter (like maxLength
				// below),  our $function might be an array.  in this case,
				// we assume that the zeroth index is the name of the
				// function to call and all subsequent indices are the
				// parameters.

				$parameters = array_slice($function, 1);
				$function = $function[0];
			}

			if (!$this->validate($value, $function, ...$parameters)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param        $value
	 * @param string $function
	 * @param array  $parameters
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	public function validate($value, string $function, ...$parameters): bool {
		if (!in_array($function, $this->validations)) {
			throw new ValidatorException(
				"Unknown validation function: $function",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}

		// some of our validators need parameters, but those that do always
		// have a default.  so, if we don't have anything to send their way,
		// we want to avoid sending them a value of nothing which would
		// override their defaults.  luckily, we have a great way of seeing
		// if our $parameters array isn't empty!

		return $this->notEmptyArray($parameters)
			? $this->{$function}($value, ...$parameters)
			: $this->{$function}($value);
	}

	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	public function validateAny($value, array $functions): bool {
		if (sizeof($functions) === 0) {
			throw new ValidatorException(
				"Cannot validate any without functions.",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}

		// this method requires that $value pass any of the validation
		// $functions.  so, we'll loop over the array and if we find a
		// successful test, we return true.  but, if we make it all the
		// way through, then none of the tests were successful, so we
		// return false.

		foreach ($functions as $function) {
			$parameters = [];

			if (is_array($function)) {

				// in order to facilitate the use of this method and
				// validation tests that require a parameter (like maxLength
				// below),  our $function might be an array.  in this case,
				// we assume that the zeroth index is the name of the
				// function to call and all subsequent indices are the
				// parameters.

				$parameters = array_slice($function, 1);
				$function = $function[0];
			}

			if ($this->validate($value, $function, $parameters)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param bool  $setType
	 * @param mixed ...$functions
	 *
	 * @return RuleSet
	 * @throws ValidatorException
	 * @throws ReflectionException
	 */
	public static function getRuleSet(bool $setType, ...$functions): RuleSet {
		if (sizeof(static::$staticValidations) === 0) {

			// if this object was instantiated before we get here, then the
			// static list of validations should already be available; it's
			// set in the constructor.  but, just in case they're not, we'll
			// double-check here to be sure.

			static::$staticValidations = (new static)->getValidations();
		}

		// this is simply a factory method that passes it's parameters right
		// over to the RuleSet object constructor.  but, first, we want to see
		// if the $functions array lists methods that exist herein.  most of
		// the time, the values in $functions are strings, but they can also
		// be arrays.  so, first, we'll transform it a bit to do our work
		// below.

		$validations = array_map(function($function) {

			// when a validation function requires arguments beyond the
			// value it's validating, the function name is the first index
			// of an array.  subsequent ones are those arguments, but we
			// don't worry about those for now.

			return is_array($function) ? $function[0] : $function;
		}, $functions);

		$missingMethods = array_diff($validations, static::$staticValidations);

		if (sizeof($missingMethods) !== 0) {
			$missingMethods = join(", ", $missingMethods);
			throw new ValidatorException(
				"Unknown validation function(s): $missingMethods",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}

		// we could pass $functions directly to the RuleSet constructor,
		// but then we get [[a, b, c]] where a, b, and c are our functions.
		// by sending ...$functions, the we unpack the array sending its
		// values to the constructor where they're gathered back up again
		// since the constructor is, itself, variadic.

		return new RuleSet($setType, ...$functions);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function float($value): bool {
		return !$this->array($value)
			? $this->number($value) && !$this->integer($value)
			: $this->validateArray($value, "float");
	}

	/**
	 * @param array  $values
	 * @param string $function
	 * @param array  $parameters
	 *
	 * @return bool
	 */
	private function validateArray(array $values, string $function, ...$parameters): bool {

		// this method is intentionally private so that (a) children don't
		// change it and (b) it's not recognized in the constructor as a
		// validator.

		foreach ($values as $value) {

			// for each $value in the array, we see if it passes the
			// specified function.  because not all validators require
			// additional parameters, we test to see if we should pass
			// that information along.

			$passed = $this->notEmptyArray($parameters)
				? $this->{$function}($value, ...$parameters)
				: $this->{$function}($value);

			// if something didn't pass the test, we return false immediately.
			// this should save us a few nanoseconds.

			if (!$passed) {
				return false;
			}
		}

		// if we made it to the end of the array and everything passed, then
		// we end up here.  that means the array is valid, so we return true.

		return true;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function number($value): bool {
		return !$this->array($value)
			? is_numeric($value)
			: $this->validateArray($value, "number");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function integer($value): bool {

		// at first glance, we could use intval() instead of floor().
		// then, we could also tighten up our comparison by using ===
		// instead of ==.  but, intval(4.0) === 4 would report false.
		// by using floor(), instead, we get a true result in such
		// cases.

		return !$this->array($value)
			? $this->number($value) && floor($value) == $value
			: $this->validateArray($value, "integer");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function positive($value): bool {
		return !$this->array($value)
			? $this->number($value) && $value > 0
			: $this->validateArray($value, "positive");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function negative($value): bool {
		return !$this->array($value)
			? $this->number($value) && $value < 0
			: $this->validateArray($value, "negative");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function nonZero($value): bool {

		// sometimes it's handy to test that something is not zero, just
		// like we want to test above that it is.

		return !$this->array($value)
			? $this->number($value) && !$this->zero($value)
			: $this->validateArray($value, "nonZero");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function zero($value): bool {

		// like when we tested our integer, we won't use === here
		// because 0.0 === 0 is actually false.  but, 0.0 == 0 is
		// true, so that's our comparison.

		return !$this->array($value)
			? $this->number($value) && $value == 0
			: $this->validateArray($value, "zero");
	}

	/**
	 * @param     $value
	 * @param int $maxLength
	 *
	 * @return bool
	 */
	protected function maxLength($value, int $maxLength): bool {
		return !$this->array($value)
			? $this->string($value) && strlen($value) <= $maxLength
			: $this->validateArray($value, "maxLength", $maxLength);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function string($value): bool {
		return !$this->array($value)
			? is_string($value)
			: $this->validateArray($value, "string");
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function notEmpty($value): bool {
		return $this->array($value)
			? $this->notEmptyArray($value)
			: $this->notEmptyString($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function array($value): bool {
		return is_array($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function notEmptyArray($value): bool {
		return $this->array($value) && !$this->emptyArray($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function notEmptyString($value): bool {
		return $this->string($value) && !$this->empty($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function empty($value): bool {
		return $this->array($value)
			? $this->emptyArray($value)
			: $this->emptyString($value);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function emptyArray($value): bool {
		if ($this->array($value)) {

			// if there's nothing in the array, then we can feel
			// confident that it's empty.  we'll return true here
			// to avoid doing the work below.

			if (sizeof($value) === 0) {
				return true;
			}

			// just because an array has indices, doesn't mean they
			// contain values.  if we're here, we're going to flatten
			// the array and then join it into a string.  if that
			// string is empty, then the values in the array were
			// empty, too.

			$flattenedArray = [];
			array_walk_recursive($value, function($x) use (&$flattenedArray) {
				$flattenedArray[] = $x;
			});

			// now, if we join $flattenedArray using the empty string as
			// our separator, we can test if the resulting string is empty.
			// if that's true, then the array that contained those empty
			// values making up this string was also empty.

			return $this->emptyString(join("", $flattenedArray));
		}

		// if we're here, then $value wasn't even an array.  we'll just
		// return false because if it's not an array, it can't be an empty
		// one.

		return false;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function emptyString($value): bool {

		// for our purposes, being comprised entirely of whitespace is
		// just as good as being empty.  so, we replace \s characters
		// with nothing and see if the length of that string is zero.

		return $this->string($value) && strlen(preg_replace("/\s+/", "", $value)) === 0;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function email($value): bool {
		return !$this->array($value)
			? (bool) filter_var($value, FILTER_VALIDATE_EMAIL)
			: $this->validateArray($value, "email");
	}

	/**
	 * @param     $value
	 * @param int $flags
	 *
	 * @return bool
	 */
	protected function url($value, int $flags = FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED): bool {
		return !$this->array($value)
			? (bool) filter_var($value, FILTER_VALIDATE_URL, $flags)
			: $this->validateArray($value, "url", $flags);
	}

	/**
	 * @param        $value
	 * @param string $format
	 *
	 * @return bool
	 */
	protected function time($value, string $format = "g:i A"): bool {

		// times can be validated just like dates; we just specify our
		// format when we call the other function.

		return $this->date($value, $format);
	}

	/**
	 * @param        $value
	 * @param string $format
	 *
	 * @return bool
	 */
	protected function date($value, $format = "m/d/Y"): bool {
		return !$this->array($value)

			// strtotime() should give us a timestamp or false.  if it's
			// false, then the date becomes 12/31/1969.  since they probably
			// didn't enter that date, it wont' match and the validation
			// fails.

			? date($format, strtotime($value)) === $value
			: $this->validateArray($value, "date", $format);
	}

	/**
	 * @param string $name
	 * @param int    $size
	 *
	 * @return bool
	 */
	protected function uploadedFileSize(string $name, int $size): bool {
		$valid = false;

		if ($this->uploadedFile($name)) {

			// now that we know this file exists, we'll see if it's size is
			// less than the $size we were sent here.  since we can't always
			// trust that the posted information hasn't been messed with,
			// we'll get the size right from the disk.

			$valid = filesize($_FILES[$name]["tmp_name"]) <= $size;
		}

		return $valid;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	protected function uploadedFile(string $name): bool {

		// the existence of an uploaded file is determined by the existence
		// of the $name index within $_FILES.  so, this is a problem for
		// isset().

		return isset($_FILES[$name]);
	}

	/**
	 * @param string $name
	 * @param array  $types
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	protected function uploadedFileType(string $name, ...$types): bool {
		$valid = false;

		if ($this->uploadedFile($name)) {

			// the list of $types has MIME types against which we need to
			// test the uploaded file's type.  we'll have the Mimey object
			// to get its type since we can't always rely on the file info
			// extension being available.

			$valid = class_exists("finfo")
				? $this->checkFileTypeWithFinfo($name, $types)
				: $this->checkFileTypeWithMimey($name, $types);
		}

		return $valid;
	}

	/**
	 * @param string $name
	 * @param array  $types
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	protected function checkFileTypeWithFinfo(string $name, array $types): bool {

		// this is the preferred method to check file types because we can
		// pass it the direct link to the file itself and it identifies the
		// type from there.  this should mean that even files from Macs,
		// i.e. without extensions, should be identifiable.

		$info = new \finfo(FILEINFO_MIME_TYPE);
		$type = $info->file($_FILES[$name]["tmp_name"]);

		if ($type === false) {
			throw new ValidatorException("Cannot identify file type.",
				ValidatorException::MIME_NOT_FOUND);
		}

		return in_array($type, $types);
	}

	/**
	 * @param string $name
	 * @param array  $types
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	protected function checkFileTypeWithMimey(string $name, array $types): bool {

		// Mimey isn't as slick as finfo because it focuses on extensions.
		// since Macs don't use extensions, this isn't foolproof.  hence,
		// the need to test and, maybe, throw an Exception.

		$mimey = new MimeTypes();
		$extension = pathinfo($_FILES[$name]["name"], PATHINFO_EXTENSION);

		if (empty($extension)) {
			throw new ValidatorException("Cannot identify file extension.",
				ValidatorException::NO_EXTENSION);
		}

		$type = $mimey->getMimeType($extension);

		if (empty($type)) {
			throw new ValidatorException("Cannot identify file type.",
				ValidatorException::MIME_NOT_FOUND);
		}

		return in_array($type, $types);
	}
}
