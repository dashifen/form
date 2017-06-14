<?php

namespace Dashifen\Form\Builder;

use Dashifen\Exception\Exception;

class FormBuilderException extends Exception {
	public const MISSING_LEGEND = 1;
	public const MISSING_FIELD_TYPE = 2;
}
