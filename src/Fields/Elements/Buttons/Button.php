<?php

namespace Dashifen\Form\Fields\Elements\Buttons;

use Dashifen\Form\Fields\AbstractField;
use Dashifen\Form\Fields\Traits\IconTrait;
use Dashifen\Form\Fields\Traits\TypeTrait;

class Button extends AbstractField {
	use IconTrait, TypeTrait;
	
	/**
	 * @var string
	 */
	protected $defaultIcon = "fa-chevron-circle-right";
	
	/**
	 * Button constructor.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $label
	 */
	public function __construct($id, $name = "", $label = "") {
		parent::__construct($id, $name, $label);
		$this->setIcon($this->defaultIcon);
		$this->setExtraType("button");
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty(): bool {
		
		// buttons don't have values but they also cannot be empty.  so
		// we just return false here.
		
		return false;
	}
	
	/**
	 * @param array $classes
	 *
	 * @return string
	 */
	public function getLabel(array $classes = []): string {
		
		// buttons aren't the same as other fields; their labels aren't
		// <label> elements but just the value of our label property.  so,
		// we just return that now.
		
		return $this->label;
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
			$this->getExtraType(),
			$this->getClassesAsString(),
			$this->getIcon(),
			$this->getLabel()
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
