<?php

namespace Dashifen\Form\Fields\Traits;

/**
 * Trait IconTrait
 *
 * @package Dashifen\Form\Fields\Traits
 */
trait IconTrait {
	/**
	 * @var string
	 */
	protected $icon = "";
	
	/**
	 * Sets the $icon property.
	 * $icon should be the full HTML representation of the icon.
	 *
	 * @param string $icon
	 */
	public function setIcon(string $icon): void {
		$this->icon = $icon;
	}
	
	/**
	 * @return string
	 */
	public function getIcon(): string {
		return $this->icon;
	}
}
