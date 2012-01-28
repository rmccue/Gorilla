<?php
class Gorilla_Listener_Base implements PHPUnit_Framework_TestListener {
	protected $suites;
	protected $suite;

	protected $test;
	protected $failed = false;

	protected function addResult($type, PHPUnit_Framework_Test &$test, &$e, $time) {
		array_push(
			$this->suite->results->$type,
			(object) array(
				'test' => &$test,
				'exception' => &$e,
				'time' => $time
			)
		);
		$this->test->exceptions[] = array(
			'exception' => $e,
			'time' => $time,
		);
	}
	/**
	 * An error occurred.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 * @param  Exception              $e
	 * @param  float                  $time
	 */
	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->addResult('error', $test, $e, $time);
		$this->failed = true;
	}

	/**
	 * A failure occurred.
	 *
	 * @param  PHPUnit_Framework_Test                 $test
	 * @param  PHPUnit_Framework_AssertionFailedError $e
	 * @param  float                                  $time
	 */
	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {
		$this->addResult('failure', $test, $e, $time);
		$this->failed = true;
	}

	/**
	 * Incomplete test.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 * @param  Exception              $e
	 * @param  float                  $time
	 */
	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->addResult('incomplete', $test, $e, $time);
		$this->failed = true;
	}

	/**
	 * Skipped test.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 * @param  Exception              $e
	 * @param  float                  $time
	 * @since  Method available since Release 3.0.0
	 */
	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {
		$this->addResult('skipped', $test, $e, $time);
		$this->failed = true;
	}

	/**
	 * A test suite started.
	 *
	 * @param  PHPUnit_Framework_TestSuite $suite
	 * @since  Method available since Release 2.2.0
	 */
	public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
		$this->suites[$suite->getName()] = (object) array(
			//'suite' => $suite,
			'results' => (object) array(
				'error' => array(),
				'failure' => array(),
				'incomplete' => array(),
				'skipped' => array(),
				'success' => array()
			),
			'tests' => array(),
		);
		$this->suite = &$this->suites[$suite->getName()];
	}

	/**
	 * A test suite ended.
	 *
	 * @param  PHPUnit_Framework_TestSuite $suite
	 * @since  Method available since Release 2.2.0
	 */
	public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
		unset($this->suite);
	}

	/**
	 * A test started.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 */
	public function startTest(PHPUnit_Framework_Test $test) {
		$this->suite->tests[$test->getName()] = (object) array(
			//'test' => $test,
			'description' => PHPUnit_Util_Test::describe($test),
			'exceptions' => array(),
			'assertions' => 0,
			'success' => false,
		);
		$this->test = &$this->suite->tests[$test->getName()];
	}

	/**
	 * A test ended.
	 *
	 * @param  PHPUnit_Framework_Test $test
	 * @param  float                  $time
	 */
	public function endTest(PHPUnit_Framework_Test $test, $time) {
		if (!$this->failed) {
			$this->test->success = true;
			$exception = null;
			$this->addResult('success', $test, $exception, $time);
		}
		$this->failed = false;

		if ($test instanceof PHPUnit_Framework_TestCase) {
			$this->test->assertions = $test->getNumAssertions();
		}
		unset($this->test);
	}

	public function &get_result() {
		return $this->suites;
	}
}
