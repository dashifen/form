<?php

namespace Dashifen\Form\Builder;

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
	 * @return string
	 */
	public function build(): string;
}
