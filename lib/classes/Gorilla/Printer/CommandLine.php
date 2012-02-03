<?php

class Gorilla_Printer_CommandLine {
	protected $listener;
	protected $runner;

	public function __construct(&$listener, &$runner) {
		$this->listener = &$listener;
		$this->runner = &$runner;
	}

	public function print_result() {
		$results = $this->listener->get_result();

		foreach ($results as $name => $suite) {
			if ($name === 'default' && empty($suite->tests)) {
				continue;
			}

			$line = sprintf("%s (%d/%d)", $name, count($suite->results->success), count($suite->tests));

			if (count($suite->results->success) === count($suite->tests)) {
				continue;
			}

			echo PHP_EOL . $line . PHP_EOL;
			echo str_repeat("=", strlen($line)) . PHP_EOL . PHP_EOL;

			if (count($suite->results->error) > 0) {
				echo "Errors" . PHP_EOL;
				echo str_repeat('-', 6) . PHP_EOL;

				$this->print_tests($suite->results->error);

				echo PHP_EOL;
			}
			if (count($suite->results->incomplete) > 0) {
				echo "Incomplete" . PHP_EOL;
				echo str_repeat('-', 10) . PHP_EOL;

				$this->print_tests($suite->results->incomplete);
				echo PHP_EOL;
			}
			if (count($suite->results->skipped) > 0) {
				echo "Skipped" . PHP_EOL;
				echo str_repeat('-', 7) . PHP_EOL;

				$this->print_tests($suite->results->skipped);

				echo PHP_EOL;
			}
			if (count($suite->results->failure) > 0) {
				echo "Failed" . PHP_EOL;
				echo str_repeat('-', 6) . PHP_EOL;

				$this->print_tests($suite->results->failure);

				echo PHP_EOL;
			}

			//var_dump($result);
		}
	}

	protected function print_tests(&$tests) {
		foreach ($tests as $error) {
			if ($error->exception instanceof PHPUnit_Framework_Error) {
				$file = str_replace(Gorilla::$path, 'Gorilla', $error->exception->getFile());
				printf("  %s: %s (%s@%d)" . PHP_EOL, $error->test->getName(), $error->exception->getMessage(), $file, $error->exception->getLine());
			}
			elseif (method_exists($error->exception, 'getComparisonFailure') && method_exists($error->exception->getComparisonFailure(), 'toString')) {
				printf("  %s:" . PHP_EOL, $error->test->getName());

				// Handle custom assertion messages
				if (method_exists($error->test, 'getStatusMessage') && $error->test->getStatusMessage() !== '') {
					printf("    %s" . PHP_EOL, $error->test->getStatusMessage());
				}

				// Get the diff, if there is one
				$result = $error->exception->getComparisonFailure()->toString();
				$result = explode("\n", $result);
				foreach ($result as &$line) {
					$line = '    ' . $line;
				}
				$result = implode("\n", $result);
				echo $result;
			}
			elseif (method_exists($error->test, 'getStatusMessage') && $error->test->getStatusMessage() !== '') {
				// Use custom message over a normal one
				printf("  %s: %s" . PHP_EOL, $error->test->getName(), $error->test->getStatusMessage());
			}
			else {
				printf("  %s: %s" . PHP_EOL, $error->test->getName(), $error->exception->getMessage());
			}

			// Only trace if told to
			if ($this->runner->get_option('trace') === true) {
				$trace = $error->exception->getTrace();
				$this->runner->print_trace($trace);
			}
		}
	}
}