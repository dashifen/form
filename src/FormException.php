<?php

namespace Dashifen\Form;

use Dashifen\Exception\Exception as DashifenException;

class FormException extends DashifenException {
	public const NOT_A_FIELDSET = 1;
	public const NOT_A_BUTTON = 2;
}
