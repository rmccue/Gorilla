<?php

if (in_array('PHPUnit_TextUI_Command', get_declared_classes())) {
	require_once(dirname(dirname(__FILE__)) . '/classes/Gorilla.php');
	Gorilla::$runner = new Gorilla_Runner_Server($argv);
}

/**
 * Service document tests
 *
 * @package Gorilla
 * @subpackage API Tests
 */
class ServiceDocumentTest extends PHPUnit_Framework_TestCase {
	/**
	 * Constructor
	 *
	 * Set up the 
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$this->uri = Gorilla::$runner->get_option('uri');
		parent::__construct($name, $data, $dataName);
	}

	/**
	 */
	public function testAccessServiceDocument() {
		$document = Requests::get($this->uri . '/service');
		$status = sprintf('Site returned %d with message "%s"', $document->status_code, $document->body);
		$this->assertEquals(200, $document->status_code, $status);
		$this->report(self::REPORT_INFO, 'Service document found');
		
		$reader = new SimpleXMLElement($document->body);
		$reader->registerXPathNamespace('app', 'http://www.w3.org/2007/app');
		$found_collections = $reader->xpath('//app:collection');
		$collections = array();
		foreach ($found_collections as $col) {
			$title = $col->children('http://www.w3.org/2005/Atom');
			$title = $title->title;
			$accepted = array();
			// We need this because otherwise SimpleXML gets funky with >1 'accept'
			foreach($col->accept as $accept) {
				$accepted[] = (string) $accept;
			}
			$accept = implode(', ', (array) $accepted);
			$collections[] = $title . ' accepts ' . $accept;
		}
		$this->reportList(self::REPORT_INFO, 'Collections found:', $collections);
		$this->assertNotEmpty($collections);
	}

	public function testFailure() {
		throw new Gorilla_Exception_NotImplemented();
	}

	const REPORT_INFO = 'info';
	protected function report() {}
	protected function reportList() {}
}