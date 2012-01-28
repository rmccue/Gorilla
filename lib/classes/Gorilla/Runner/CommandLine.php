<?php

class Gorilla_Runner_CommandLine extends Gorilla_Runner {
	protected $args = array();

	public function __construct($args) {
		$this->args = array_slice($args, 1);
		parent::__construct();
	}

	public function load_options($opts) {
		$options = array(
			'uri' => '',
			'auth' => false
		);
		if (empty($opts['uri'])) {
			$this->usage($opts, 'URI must be specified');
		}
		$options['uri'] = $opts['uri'];

		$options['trace'] = isset($opts['trace']) ? (bool) $opts['trace'] : false;
		$options['debug'] = isset($opts['debug']) ? (bool) $opts['debug'] : false;
		
		if (!empty($opts['username']) && isset($opts['password'])) {
			$options['auth'] = array(
				'type' => 'basic',
				'user' => $opts['username'],
				'pass' => $opts['password'],
			);
		}
		$this->set_options($options);
	}
	/**
	 * Command line argument parser
	 *
	 * Will successfully parse:
	 *    -a -> 'a' => true
	 *    -b=c -> 'b' => 'c'
	 *    --def=g -> 'def' => 'g'
	 *
	 * Will not parse:
	 *    -h i (value being i for option h)
	 *    -jk (value being k for option j)
	 *    -lm (option l, option m)
	 */
	protected function parse_args($options, $long_options) {
		$opts = array();
		$method = '';
		$params = array();
		while (count($this->args) > 0) {
			$arg = array_shift($this->args);
			// Got the method, ignore everything after
			if ($arg[0] !== '-') {
				$method = $arg;
				$params = $this->args;
				break;
			}
			// Method is next, regardless of if it starts with -
			elseif ($arg === '--') {
				$method = array_shift($this->args);
				$params = $this->args;
				break;
			}
			// Long option
			elseif (strpos($arg, '--') === 0) {
				$bits = explode('=', substr($arg, 2), 2);
				if (empty($bits[1])) {
					$bits[1] = true;
				}
				list($name, $value) = $bits;
				if (array_key_exists($name, $long_options)) {
					$opts[$long_options[$name]] = $value;
				}
			}
			else {
				$bits = explode('=', substr($arg, 1), 2);
				if (empty($bits[1])) {
					$bits[1] = true;
				}
				list($name, $value) = $bits;
				if (array_key_exists($name, $options)) {
					$opts[$options[$name]] = $value;
				}
			}
		}
		return array($opts, $method, $params);
	}

	protected function usage($opts, $error = null) {
		$command = empty($opts['using_batch']) ? 'gorilla' : 'gorilla.bat';
		if ($error !== null) {
			printf("%s: %s\n", $command, $error);
		}
		printf("usage: %s [OPTION]... TEST\n\n", $command);
		echo "  -u, --uri, --url\n";
		echo "    AtomPub API URI (required)\n";
		echo "  --user\n";
		echo "    Authentication username\n";
		echo "  --pass\n";
		echo "    Authentication password\n";
		echo "  --auth-type\n";
		echo "    Authentication type (defaults to none, or HTTP Basic if\n      username is specified)\n";
		echo "\nAvailable tests:";
		foreach (Gorilla::get_tests() as $test) {
			echo "\n  " . $test;
		}
		die();
	}

	public function run() {
		$options = array(
			'u' => 'uri',
			'v' => 'debug',
		);
		$long_options = array(
			'url' => 'uri',
			'uri' => 'uri',
			'user' => 'username',
			'pass' => 'password',
			'auth-type' => 'auth',
			'win' => 'using_batch',
			'trace' => 'trace',
			'debug' => 'debug',
		);
		list($opts, $method, $params) = $this->parse_args($options, $long_options);
		if (empty($method)) {
			return $this->usage($opts);
		}
		$this->load_options($opts);
		
		//throw new Gorilla_Exception_NotImplemented();
		printf("Gorilla is beginning testing on %s..." . PHP_EOL . PHP_EOL, $this->get_option('uri'));

		$listener = new Gorilla_Listener_Base();
		$result = $this->run_tests((array) $method, $listener);
		//var_dump($listener);

		$printer = new Gorilla_Printer_CommandLine($listener, $this);
		$printer->print_result();
	}

	public function report($level, $message) {
		if ($level === Gorilla_Runner::REPORT_DEBUG && !$this->get_option('debug')) {
			return;
		}

		printf("[%s] %s" . PHP_EOL, $level, $message);
	}

	public function reportList($level, $message, $list) {
		if ($level === Gorilla_Runner::REPORT_DEBUG && !$this->get_option('debug')) {
			return;
		}

		printf("[%s] %s" . PHP_EOL, $level, $message);
		foreach ($list as $item) {
			printf("    %s" . PHP_EOL, $item);
		}
	}
}