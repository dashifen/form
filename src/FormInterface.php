<?php

namespace Dashifen\Form;

use Dashifen\Form\Fields\FieldInterface;
use Dashifen\Form\Fieldset\FieldsetInterface;

/**
 * Interface FormInterface
 *
 * @package Dashifen\Form
 */
/**
 * Interface FormInterface
 *
 * @package Dashifen\Form
 */
interface FormInterface {
	/**
	 * @param string $method
	 *
	 * @return void
	 */
	public function setMethod(string $method): void;
	
	/**
	 * @return string
	 */
	public function getMethod(): string;
	
	/**
	 * @param string $action
	 *
	 * @return void
	 */
	public function setAction(string $action): void;
	
	/**
	 * @return string
	 */
	public function getAction(): string;
	
	/**
	 * @param string $enctype
	 *
	 * @return void
	 */
	public function setEnctype(string $enctype): void;
	
	/**
	 * @return string
	 */
	public function getEnctype(): string;
	
	/**
	 * @param string $instructions
	 *
	 * @return void
	 */
	public function setInstructions(string $instructions): void;
	
	/**
	 * @return string
	 */
	public function getInstructions(): string;
	
	/**
	 * @param array $fieldsets
	 *
	 * @return void
	 * @throws FormException
	 */
	public function addFieldsets(array $fieldsets): void;
	
	/**
	 * @param FieldsetInterface $fieldset
	 *
	 * @return void
	 */
	public function addFieldset(FieldsetInterface $fieldset): void;
	
	/**
	 * @return array
	 */
	public function getFieldsets(): array;
	
	/**
	 * @param string $field
	 * @param string $value
	 *
	 * @return void
	 */
	public function addFieldValue(string $field, string $value): void;
	
	/**
	 * @param string      $field
	 * @param string      $error
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public function addFieldError(string $field, string $error, string $value = null): bool;
	
	/**
	 * @param string $instructions
	 * @param bool   $state
	 *
	 * @return void
	 */
	public function setError(string $instructions, bool $state = true): void;
	
	/**
	 * @param string $instructions
	 *
	 * @return void
	 */
	public function resetError(string $instructions): void;
	
	/**
	 * @param array $buttons
	 *
	 * @return void
	 * @throws FormException
	 */
	public function addButtons(array $buttons): void;
	
	/**
	 * @param FieldInterface $button
	 * @throws FormException
	 */
	public function addButton(FieldInterface $button): void;
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getForm(bool $display = true): string;

	/**
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasField(string $id): bool;

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	public function hasFieldOfType(string $type): bool;

	/**
	 * @param string $id
	 *
	 * @return FieldInterface|null
	 */
	public function getField(string $id): ?FieldInterface;

	/**
	 * @return array
	 */
	public function getFields(): array;

	/**
	 * @param string $jsonForm
	 *
	 * @return FormInterface
	 */
	public static function parse(string $jsonForm): FormInterface;
}
