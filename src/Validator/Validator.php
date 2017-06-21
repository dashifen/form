<?php

namespace Dashifen\Form\Validator;

/**
 * Class AbstractValidator
 *
 * @package Dashifen\Form\Validator
 */
class Validator implements ValidatorInterface {
	
	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;
	
	/**
	 * Validator constructor.
	 */
	public function __construct() {
		$this->reflection = new \ReflectionClass($this);
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
		
		try {
			return $method->invoke($this, $value, ...$parameters);
		} catch (\ReflectionException $reflectionException) {
			throw new ValidatorException(
				"Unable to validate '$value' with '$function'",
				ValidatorException::UNABLE_TO_VALIDATE,
				$reflectionException
			);
		}
	}
	
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
	protected function float($value): bool {
		return $this->number($value) && !$this->integer($value);
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
	protected function zero($value): bool {
		
		// like when we tested our integer, we won't use === here
		// because 0.0 === 0 is actually false.  but, 0.0 == 0 is
		// true, so that's our comparison.
		
		return $this->number($value) && $value == 0;
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
	 * @return mixed
	 */
	protected function email($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * @param     $value
	 * @param int $flags
	 *
	 * @return mixed
	 */
	protected function url($value, int $flags = FILTER_FLAG_SCHEME_REQUIRED & FILTER_FLAG_HOST_REQUIRED) {
		return filter_var($value, FILTER_VALIDATE_URL, $flags);
	}
}
