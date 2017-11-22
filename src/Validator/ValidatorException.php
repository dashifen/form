<?php

namespace Dashifen\Form\Validator;

use Dashifen\Exception\Exception;

class ValidatorException extends Exception {
	public const UNKNOWN_FUNCTION = 1;
	public const INVALID_RETURN_TYPE = 2;
	public const UNABLE_TO_VALIDATE = 3;
	public const NO_EXTENSION = 4;
	public const MIME_NOT_FOUND = 5;
}
