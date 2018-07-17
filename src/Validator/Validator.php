<?php

namespace Dashifen\Form\Validator;

use Mimey\MimeTypes;

/**
 * Class AbstractValidator
 * @package Dashifen\Form\Validator
 */
class Validator implements ValidatorInterface {
	
	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * Validator constructor.
	 * @throws \ReflectionException
	 */
	public function __construct() {
		$this->reflection = new \ReflectionClass($this);
	}

	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 * @throws \ReflectionException
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
	 * @throws \ReflectionException
	 */
	public function validate($value, string $function, ...$parameters): bool {
		if (!$this->reflection->hasMethod($function)) {
			throw new ValidatorException(
				"Unknown validation function: $function",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}
		
		// now that we've confirmed that this object has a method named
		// $function, we have to be sure that it returns a boolean value.
		// if not, we throw a different exception.
		
		$method = new \ReflectionMethod($this, $function);
		if (($type = $method->getReturnType()) != "bool") {
			throw new ValidatorException(
				"Invalid return type: $type",
				ValidatorException::INVALID_RETURN_TYPE
			);
		}
		
		// finally, we invoke our $method.  if we can't do so, we want to
		// catch the ReflectionException that's thrown and "convert" it to
		// a ValidatorException in the catch block below.
		
		
		$method->setAccessible(true);
		return $method->invoke($this, $value, ...$parameters);
	}

	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 * @throws \ReflectionException
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
	 */
	public static function geetRuleSet(bool $setType, ...$functions): RuleSet {

		// this is simply a factory method that passes it's parameters right
		// over to the RuleSet object constructor.  but, first, we want to see
		// if the $functions array lists methods that exist herein.  we use
		// array_diff() to do that work; it returns any information in the
		// first argument that's not found in the latter one.  so, in this
		// case, it's items in $functions that aren't found in this class's
		// methods.

		$missingMethods = array_diff($functions, get_class_methods(static::class));

		if (sizeof($missingMethods) !== 0) {
			$missingMethods = join(", ", $missingMethods);
			throw new ValidatorException(
				"Unknown validation function(s): $missingMethods",
				ValidatorException::UNKNOWN_FUNCTION
			);
		}

		return new RuleSet($setType, $functions);
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function float($value): bool {
		return $this->number($value) && !$this->integer($value);
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function number($value): bool {
		return is_numeric($value);
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
		
		return $this->number($value) && floor($value) == $value;
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function positive($value): bool {
		return $this->number($value) && $value > 0;
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function negative($value): bool {
		return $this->number($value) && $value < 0;
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function nonZero($value): bool {
		
		// sometimes it's handy to test that something is not zero, just
		// like we want to test above that it is.
		
		return $this->number($value) && !$this->zero($value);
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
		
		return $this->number($value) && $value == 0;
	}
	
	/**
	 * @param     $value
	 * @param int $maxLength
	 *
	 * @return bool
	 */
	protected function maxLength($value, int $maxLength): bool {
		return $this->string($value) && strlen($value) <= $maxLength;
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function string($value): bool {
		return is_string($value);
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
	protected function emptyArray($value): bool {
		return $this->array($value) && sizeof($value) === 0;
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
	protected function emptyString($value): bool {
		return $this->string($value) && strlen(preg_replace("/\s+/", "", $value)) === 0;
	}
	
	/**
	 * @param $value
	 *
	 * @return bool
	 */
	protected function email($value): bool {
		return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * @param     $value
	 * @param int $flags
	 *
	 * @return bool
	 */
	protected function url($value, int $flags = FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED): bool {
		return (bool)filter_var($value, FILTER_VALIDATE_URL, $flags);
	}
	
	/**
	 * @param        $value
	 * @param string $format
	 *
	 * @return bool
	 */
	protected function time($value, string $format = "g:i A"): bool {
		
		// times can be validated just like dates; we just specif our
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
		
		// for dates, we do something weird.  first, we use strtotime() to
		// confirm that what we have is a readable datetime format.
		
		$timestamp = strtotime($value);
		if ($timestamp === false) {
			return false;
		}
		
		// if we didn't return above, then we have a valid datetime format
		// in $value.  next, we'll want to re-create our date using $format
		// and see if that created date matches our value.  if so, then we
		// we have a valid date in the right format and we'll return true.
		
		return date($format, $timestamp) === $value;
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
