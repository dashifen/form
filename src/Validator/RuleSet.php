<?php

namespace Dashifen\Form\Validator;

/**
 * Class RuleSet
 * @package Dashifen\Form\Validator
 */
class RuleSet {
	const ALL = true;
	const ANY = false;

	/**
	 * @var array
	 */
	protected $rules = [];

	/**
	 * @var bool
	 */
	protected $type = self::ALL;

	/**
	 * ValidationRuleSet constructor.
	 *
	 * @param bool $type
	 * @param mixed ...$rules
	 */
	public function __construct(bool $type, ...$rules) {
		$this->setRules($rules);
		$this->setType($type);
	}

	/**
	 * @return array
	 */
	public function getRules(): array {
		return $this->rules;
	}

	/**
	 * @param array $rules
	 */
	public function setRules(array $rules): void {
		$this->rules = $rules;
	}

	/**
	 * @return bool
	 */
	public function getType(): bool {
		return $this->type;
	}

	/**
	 * @param bool $type
	 */
	public function setType(bool $type): void {
		$this->type = $type;
	}
}