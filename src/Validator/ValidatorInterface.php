<?php

namespace Dashifen\Form\Validator;

/**
 * Interface ValidatorInterface
 *
 * @package Dashifen\Form\Validator
 */
interface ValidatorInterface {
	/**
	 * @param        $value
	 * @param string $function
	 * @param array  $parameters
	 *
	 * @return bool
	 */
	public function validate($value, string $function, ...$parameters): bool;
	
	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	public function validateAll($value, array $functions): bool;
	
	/**
	 * @param       $value
	 * @param array $functions
	 *
	 * @return bool
	 * @throws ValidatorException
	 */
	public function validateAny($value, array $functions): bool;

	/**
	 * @param bool  $setType
	 * @param array ...$functions
	 *
	 * @return RuleSet
	 */
	public static function getRuleSet(bool $setType, ...$functions): RuleSet;

}
