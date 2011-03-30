<?php

abstract class Gorilla_Runner {
	protected $options = array();

	public function __construct() {
		// No-op
	}

	abstract protected function load_options($opts);

	public function get_options() {
		if (empty($this->options)) {
			$this->load_options();
		}
		return $this->options;
	}
	public function get_option($key) {
		if (empty($this->options)) {
			$this->load_options();
		}

		if (!empty($this->options[$key])) {
			return $this->options[$key];
		}
		return null;
	}
	public function set_options($options) {
		$this->options = $options;
	}

	//abstract public function report();

	abstract public function run();

	public function exception($exception) {
		echo "\nGorilla has tripped over!\n\n";
		echo "An exception occurred:\n";
		printf("  %s (code %d)\n\n", $exception->getMessage(), $exception->getCode());
		echo "Traceback:\n";
		$traced = $exception->getTrace();
		foreach ($traced as $num => $trace) {
			$func = $trace['function'];
			if (isset($trace['class'])) {
				$func = $trace['class'] . $trace['type'] . $trace['function'];
			}
			printf("  #%d - %s @ L%d: %s()\n", $num, str_replace(Gorilla::$path, 'Gorilla', $trace['file']), $trace['line'], $func);
		}
	}
	protected function run_tests($tests, $printer) {
		$suite = new PHPUnit_Framework_TestSuite('default');
		foreach ($tests as $case)
			$suite->addTestSuite($case);

		#return PHPUnit::run($suite);
		$result = new PHPUnit_Framework_TestResult;
		$result->addListener($printer);
		return array($suite->run($result), $printer);
	}
}