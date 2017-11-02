<?php

namespace Dashifen\Form\Builder;

use Dashifen\Form\FormInterface;

interface FormBuilderInterface {
	/**
	 * @param array  $description
	 * @param string $object
	 */
	public function openForm(array $description = [], string $object = 'Dashifen\Form\Form');
	
	/**
	 * @param array  $description
	 * @param string $object
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function openFieldset(array $description = [], string $object = 'Dashifen\Form\Fieldset\Fieldset'): void;
	
	/**
	 * @param array  $description
	 * @param string $object
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function addField(array $description = [], string $object = 'Dashifen\Form\Fields\AbstractField'): void;
	
	/**
	 * @param string $object
	 *
	 * @return FormInterface
	 */
	public function buildForm(string $object = 'Dashifen\Form\Form'): FormInterface;
	
	/**
	 * @param string $object
	 *
	 * @return FormInterface
	 * @deprecated
	 */
	public function getForm(string $object = 'Dashifen\Form\Form'): FormInterface;
	
	/**
	 * @return string
	 */
	public function getFormJson(): string;
	
	/**
	 * @return string
	 * @deprecated 1.9.0 use getFormJson() instead.
	 */
	public function build(): string;
}
