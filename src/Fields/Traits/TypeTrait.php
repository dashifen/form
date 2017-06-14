<?php

namespace Dashifen\Form\Fields\Traits;

/**
 * Class TypeTrait
 *
 * Some fields, in addition to the type property which identifies the
 * field itself, need a type attribute (e.g. buttons, inputs).  For that
 * we use this trait which gives us an extra property that can be used
 * by fields that need it.
 *
 * @package Dashifen\Form\Fields\Traits
 */
trait TypeTrait {
	/**
	 * @var string $extraType ;
	 */
	protected $extraType = "";
	
	/**
	 * @return mixed
	 */
	public function getExtraType(): string {
		return $this->extraType;
	}
	
	/**
	 * @param mixed $extraType
	 */
	public function setExtraType(string $extraType): void {
		$this->extraType = $extraType;
	}
}
