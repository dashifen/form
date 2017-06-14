<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\Traits\IconTrait;
use Dashifen\Form\Fields\Traits\TypeTrait;

class Button extends AbstractField {
	use IconTrait, TypeTrait;
	
	protected $defaultIcon = "fa-chevron-circle-right";
	
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setIcon($this->defaultIcon);
		$this->setExtraType("button");
	}
	
	/**
	 * Sets the $icon property.
	 * $icon must be a FontAwesome class for the icon.
	 *
	 * @param string $icon
	 */
	public function setIcon(string $icon): void {
		
		// we use three specific icons for our reset, submit, and
		// "regular" buttons as described in the following array.
		// for now, we assume that the programmers don't mix things
		// up (e.g. with save icon on a reset button), but if they
		// set something else, we'll handle the default here.
		
		if (!in_array($icon, ["fa-undo", "fa-save", "fa-chevron-circle-right"])) {
			$icon = $this->defaultIcon;
		}
		
		$this->icon = $icon;
	}
	
	/**
	 * Sets the $attributeType property.
	 * This overrides the method from our TypeTrait to limit to appropriate
	 * button types.
	 *
	 * @param string $extraType
	 */
	public function setExtraType(string $extraType): void {
		if (!in_array($extraType, ["button", "reset", "submit"])) {
			$extraType = "button";
		}
		
		$this->extraType = $extraType;
	}
	
	public function getField(bool $display = false): string {
		$field = sprintf('<button type="%s" class="%s">%s%s</button>',
			$this->extraType,
			$this->getClassesAsString(),
			$this->getIcon(),
			$this->label
		);
		
		return parent::display($field, $display);
	}
	
	/**
	 * Returns the icon.
	 * Using the $icon property set above, constructs a FontAwesome icon.
	 *
	 * @return string
	 */
	public function getIcon(): string {
		return sprintf('<i class="fa fa-fw %s" aria-hidden="true"></i>', $this->icon);
	}
}
