<?php

namespace Dashifen\Form\Fields;

use Dashifen\Exception\Exception;

class FieldException extends Exception {
	public const OPTIONS_REQUIRED  = 1;
	public const OPTIONS_TOO_DEEP  = 2;
	public const INVALID_CLASSES   = 3;
	public const UNKNOWN_FIELD     = 4;
	public const NOT_ENOUGH_VALUES = 5;
	public const INVALID_VALUE     = 6;
	public const UNKNOWN_PROPERTY  = 7;
}
