<?php

namespace Dashifen\Form\Fieldset;

use Dashifen\Form\Fields\FieldInterface;

/**
 * Interface FieldsetInterface
 *
 * @package Dashifen\Form
 */
interface FieldsetInterface {
	/**
	 * @param string $class
	 */
	public function setClass(string $class): void;
	
	/**
	 * @param array $classes
	 */
	public function setClasses(array $classes): void;
	
	/**
	 * @return string
	 */
	public function getClassesAsString(): string;
	
	/**
	 * @return array
	 */
	public function getClasses(): array;
	
	/**
	 * @param string $instructions
	 */
	public function setInstructions(string $instructions): void;
	
	/**
	 * @return string
	 */
	public function getInstructions(): string;
	
	/**
	 * @param FieldInterface $field
	 */
	public function addField(FieldInterface $field): void;
	
	/**
	 * @param array $fields
	 * @throws FieldsetException
	 */
	public function addFields(array $fields): void;
	
	/**
	 * @return array
	 */
	public function getFields(): array;
	
	/**
	 * @param string $fieldId
	 *
	 * @return bool
	 */
	public function hasField(string $fieldId): bool;
	
	/**
	 * @param string      $fieldId
	 * @param string      $error
	 * @param string|null $value
	 *
	 * @return bool
	 */
	public function addError(string $fieldId, string $error, string $value = null): bool;
	
	/**
	 * @param string $fieldId
	 * @param string $value
	 */
	public function addValue(string $fieldId, string $value): void;
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getFieldset(bool $display = false): string;
	
	/**
	 * @param string $jsonFieldset
	 *
	 * @return FieldsetInterface
	 * @throws FieldsetException
	 */
	public static function parse(string $jsonFieldset): FieldsetInterface;
}
