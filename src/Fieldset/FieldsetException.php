<?php

namespace Dashifen\Form\Fieldset;

use Dashifen\Exception\Exception;

class FieldsetException extends Exception {
	public const NOT_A_FIELD = 1;
	public const INVALID_CLASSES = 2;
}
