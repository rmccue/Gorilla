<?php

class Gorilla {
	public static $path;
	public static $runner;
	public static function autoload($class) {
		$file_name = str_replace('_', '/', $class);
		if (substr_compare($class, 'Test', -4, 4) === 0) {
			$path = Gorilla::$path . '/lib/tests/' . $file_name . '.php';
		}
		else {
			$path = Gorilla::$path . '/lib/classes/' . $file_name . '.php';
		}
		if (file_exists($path)) {
			include_once($path);
		}
	}
	public static function run() {
		Gorilla::$runner->run();
	}
	public static function exception($exception) {
		Gorilla::$runner->exception($exception);
	}
	/**
	 * Error handler
	 *
	 * Currently disabled until it works
	 */
	public static function error($errno, $errstr, $errfile, $errline ) {
		$show = (bool) (error_reporting() & $errno);
		if (!$show) {
			return;
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	public static function get_tests() {
		$tests = array();
		foreach(glob(Gorilla::$path . '/lib/tests/*.php') as $file) {
			$name = str_replace(Gorilla::$path . '/lib/tests/', '', $file);
			$name = str_replace('.php', '', $name);
			$tests[] = $name;
		}
		return $tests;
	}

	public static function load_phpunit() {
		if (!class_exists('PHPUnit_TestCase')) {
			include 'PHPUnit/Autoload.php';
			if (!class_exists('PHPUnit_TestCase')) {
				throw new Exception('PHPUnit could not be loaded');
			}
		}
	}
}

Gorilla::$path = dirname(dirname(dirname(__FILE__)));
spl_autoload_register(array('Gorilla', 'autoload'));
Gorilla::load_phpunit();

set_exception_handler(array('Gorilla', 'exception'));