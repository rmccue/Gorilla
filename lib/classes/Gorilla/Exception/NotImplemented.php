<?php

/**
 * Exception for methods that have not been implemented
 *
 * @package Gorilla
 * @subpackage Core
 */
class Gorilla_Exception_NotImplemented extends Exception {
	public function __construct($class = null, $method = null, $code = 0, Exception $previous = null) {
		if ($class === null) {
			$backtrace = debug_backtrace();
			$class = get_class($backtrace[1]['object']);
			$method = $backtrace[1]['function'];
		}
		if (is_object($class)) {
			$class = get_class($class);
		}
		$message = sprintf('%s::%s not implemented', $class, $method);
		parent::__construct($message, $code, $previous);
	}
}