<?php

namespace Dashifen\Form\Validator;

use Dashifen\Exception\Exception;

class ValidatorException extends Exception {
	public const UNKNOWN_FUNCTION = 1;
	public const INVALID_RETURN_TYPE = 2;
	public const UNABLE_TO_VALIDATE = 3;
}
