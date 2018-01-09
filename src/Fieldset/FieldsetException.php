<?php

namespace Dashifen\Form\Fieldset;

use Dashifen\Exception\Exception;

class FieldsetException extends Exception {
	public const NOT_A_FIELD = 1;
	public const INVALID_CLASSES = 2;
	public const NOT_A_FIELDSET = 3;
	public const NEITHER_FIELD_NOR_FIELDSET = 4;
}
