<?php

namespace Dashifen\Form\Fields;

/**
 * Interface FieldInterface
 *
 * @package Dashifen\Form\Fields
 */
interface FieldInterface {
	public const REQUIRED = true;
	public const OPTIONAL = false;
	
	/**
	 * @param string $id;
	 *
	 * @return bool
	 */
	public function is(string $id): bool;
	
	/**
	 * @return bool
	 */
	public function isLocked(): bool;
	
	/**
	 * @return bool
	 */
	public function isEmpty(): bool;

	/**
	 * @param string $id
	 */
	public function setId(string $id): void;
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getId(string $suffix = ""): string;
	
	/**
	 * @param string $name
	 */
	public function setName(string $name): void;
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getName(string $suffix = ""): string;
	
	/**
	 * @param string $type
	 */
	public function setType(string $type = ""): void;
	
	/**
	 * @return string
	 */
	public function getType(): string;
	
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
	 * @param bool $required
	 */
	public function setRequired(bool $required): void;
	
	/**
	 * @return bool
	 */
	public function getRequired(): bool;
	
	/**
	 * @param string $value
	 */
	public function setValue(string $value): void;
	
	/**
	 * @return string
	 */
	public function getValue(): string;
	
	/**
	 * @param array $options
	 */
	public function setOptions(array $options): void;
	
	/**
	 * @return array
	 */
	public function getOptions(): array;
	
	/**
	 * @param array $additionalAttributes
	 */
	public function setAdditionalAttributes(array $additionalAttributes): void;
	
	/**
	 * @return array
	 */
	public function getAdditionalAttributes(): array;
	
	/**
	 * @param array $validation
	 */
	public function setValidation(array $validation): void;
	
	/**
	 * @return array
	 */
	public function getValidation(): array;
	
	/**
	 * @param bool $error
	 */
	public function setError(bool $error): void;
	
	/**
	 * @return bool
	 */
	public function getError(): bool;
	
	/**
	 * @param string $errorMessage
	 */
	public function setErrorMessage(string $errorMessage): void;
	
	/**
	 * @return string
	 */
	public function getErrorMessage(): string;
	
	/**
	 * @return int
	 */
	public function getProductFieldCount(): int;
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string;
	
	/**
	 * @param string $jsonField
	 *
	 * @return FieldInterface
 	 * @throws FieldException
	 */
	public static function parse(string $jsonField): FieldInterface;
	
	/**
	 * @param string $type
	 *
	 * @return string
	 * @throws FieldException
	 */
	public static function getNamespacedType(string $type): string;
}
