<?php

namespace Dashifen\Form\Fields;

use Dashifen\Exception\Exception;

class FieldException extends Exception {
	public const OPTIONS_REQUIRED = 1;
	public const OPTIONS_TOO_DEEP = 2;
}
