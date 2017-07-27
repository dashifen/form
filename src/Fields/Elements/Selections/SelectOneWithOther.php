<?php

namespace Dashifen\Form\Fields\Elements\Selections;

/**
 * Class SelectOneWithOther
 *
 * The SelectOneWithOther class is a select element with an input[type=text]
 * field immediately after it.  That field is displayed when the select's
 * value is "?" and hidden otherwise.
 *
 * @package Dashifen\Form\Fields\Elements\Selections
 */
class SelectOneWithOther extends SelectOne {
	/**
	 * @var string
	 */
	protected $other = "";
	
	/**
	 * @var array $values
	 */
	protected $values = null;
	
	/**
	 * @var int
	 */
	protected $fieldElementCount = 2;
	
	/**
	 * @return array
	 */
	public function getClasses(): array {
		
		// we want to be sure that we have our with-other class but then
		// our parent can take care of the rest.
		
		$this->addWithOtherClass();
		return parent::getClasses();
	}
	
	/**
	 * @return void
	 */
	protected function addWithOtherClass(): void {
		
		// if our with-other class is not already a part of this object,
		// then we'll add it here.  this function may get called a few times,
		// but we only add it if needed the first time.
		
		if (!in_array("with-other", $this->classes)) {
			$this->classes[] = "with-other";
		}
	}
	
	/**
	 * @param bool $display
	 *
	 * @return string
	 */
	public function getField(bool $display = false): string {
		
		// to separate our value from our other value, we can use our parent's
		// transformJsonValue() method.  the only thing we need to do is pass
		// it a different default to make sure each of these start out empty
		// if there's no current value.
		
		list($this->value, $this->other) = $this->transformJsonValue([
			"known"   => "",
			"unknown" => "",
		]);
		
		$field = parent::getField(false);
		
		// to construct this field, we have to add an input[type=text] field
		// following the select element that $field currently contains.  most
		// of what we do here is pretty obvious, but we do want to allow a
		// placeholder if they want to use it.
		
		$attributes = $this->getAttributesAsString(["placeholder"]);
		$format = '<input type="text" id="%s" name="%s" class="%s other other-hidden" %s value="%s">';
		$input = sprintf($format, $this->getId("unknown"), $this->getName("unknown"), $this->getClassesAsString(), $attributes, $this->getOther());
		$field = str_replace("</select>", "$input</select>", $field);
		
		// but, that's not quite enough; we also need to add the behavior that
		// toggles the display of our other field.  we'll add an onchange
		// attribute to our select element and some JS to our DOM as follows.
		
		$jsFunction = uniqid("selectWithOther_");
		$replacement = '<select onchange="' . $jsFunction . '(this)" ';
		$field = str_replace("<select ", $replacement, $field);
		$field .= $this->getJavaScript($jsFunction);
		
		return parent::display($field, $display);
	}
	
	/**
	 * @param array $default
	 *
	 * @return array
	 */
	protected function transformJsonValue(array $default = []): array {
		return array_values(parent::transformJsonValue($default));
	}
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getId(string $suffix = "known"): string {
		return sprintf("%s-%s", $this->id, $suffix);
	}
	
	/**
	 * @param string $suffix
	 *
	 * @return string
	 */
	public function getName(string $suffix = "known"): string {
		return sprintf("%s-%s", $this->name, $suffix);
	}
	
	/**
	 * @return string
	 */
	public function getClassesAsString(): string {
		
		// like when getting an array of our classes, we want to be sure
		// that we have our with-other class, but then the SelectOne's
		// behavior is good enough.
		
		$this->addWithOtherClass();
		return parent::getClassesAsString();
	}
	
	/**
	 * @return string
	 */
	public function getOther(): string {
		return $this->other;
	}
	
	/**
	 * @param string $other
	 */
	public function setOther(string $other) {
		$this->other = $other;
	}
	
	/**
	 * @param $functionName
	 *
	 * @return string
	 */
	protected function getJavaScript($functionName) {
		
		// our javascript is pretty simple for this behavior.  the function
		// receives the select element as a parameter which we can use to
		// quickly identify the other element and its value.  then, if the
		// value is "?" we want to remove the other-hidden and add it other-
		// wise.  the toggle() method of the classList object handles that.
		
		ob_start(); ?>
		
		<script type="text/javascript">
			function <?= $functionName ?>(select) {
				var other = select.nextElementSibling;
				var value = select.options[select.selectedIndex].value;
				other.classList.toggle("other-hidden", value !== "?");
			}
		</script>
		
		<?php return ob_get_clean();
	}
	
	/**
	 * @return string
	 */
	protected function getDefaultDisplay(): string {
		
		// our parent will determine the default display based on the number
		// of options to display.  here, though, we just want a <select>
		// element to facilitate easy identification of when things change.
		
		return "select";
	}
}
