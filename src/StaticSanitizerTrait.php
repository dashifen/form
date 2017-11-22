<?php

namespace Dashifen\Form;

/**
 * Trait StaticSanitizerTrait
 *
 * this trait as two static methods to objects which use it.  the methods
 * are static because the static parse() methods of various objects within
 * this package may need to use them.
 *
 * @package Dashifen\Form
 */
trait StaticSanitizerTrait {
	/**
	 * @param string $string
	 * @param string $replacement
	 *
	 * @return string
	 */
	public static function sanitizeString(string $string, string $replacement = "-"): string {

		// to sanitize a string means to remove non-word characters and
		// replace them with what we've specified as our $replacement.
		// this may not be a recoverable change; simply unsanitizing a
		// sanitized string may not result in the original string.

		return strtolower(preg_replace("/\W+/", $replacement, $string));
	}

	public static function unsanitizeString(string $string, string $pattern = '/[_-]/'): string {

		// unsanitizing a string means to replace the pattern with a space
		// and then capitalize resulting words.  i.e. we take a word that is
		// sanitary and we dirty it up a bit with some whitespace.

		return ucwords(preg_replace($pattern, " ", $string));
	}
}
