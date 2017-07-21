<?php

namespace Dashifen\Form\Fields\Elements\Entries;

/**
 * Class Password
 *
 * @package Dashifen\Form\Fields\Elements\Entries
 */
class Password extends Text {
	
	// here's a weird one:  we actually don't need to do anything
	// here.  simply by creating this object, the setType() method
	// will default to "password" instead of "text."  since that's
	// the only difference between password and text fields, we're
	// done!
	
}
