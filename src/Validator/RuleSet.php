<?php

namespace Dashifen\Form\Validator;

class RuleSet {
	const ALL = true;
	const ANY = false;

	/**
	 * @var array
	 */
	public $rules = [];

	/**
	 * @var bool
	 */
	public $type = self::ALL;

	/**
	 * ValidationRuleSet constructor.
	 *
	 * @param bool $type
	 * @param mixed ...$rules
	 */
	public function __construct(bool $type, ...$rules) {
		$this->type = (bool) $type;
		$this->rules = $rules;
	}
}