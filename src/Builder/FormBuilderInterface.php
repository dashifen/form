<?php

namespace Dashifen\Form\Builder;

interface FormBuilderInterface {
	/**
	 * @param array  $description
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function openFieldset(array $description): void;
	
	/**
	 * @param array  $description
	 *
	 * @return void
	 * @throws FormBuilderException
	 */
	public function addField(array $description): void;
	
	/**
	 * @return string
	 * @throws FormBuilderException
	 */
	public function build(): string;
}
