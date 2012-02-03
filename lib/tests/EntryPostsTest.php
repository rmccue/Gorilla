<?php

if (in_array('PHPUnit_TextUI_Command', get_declared_classes())) {
	require_once(dirname(dirname(__FILE__)) . '/classes/Gorilla.php');
	Gorilla::$runner = new Gorilla_Runner_Server($argv);
}

/**
 * Post document tests
 *
 * @package Gorilla
 * @subpackage API Tests
 */
class EntryPostsTest extends PHPUnit_Framework_TestCase {

	/**
	 * Constructor
	 *
	 * Set up the 
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$this->uri = Gorilla::$runner->get_option('uri');
		$this->auth = Gorilla::$runner->get_option('auth');
		$this->document = Requests::get($this->uri . '/service', array(), array(), $this->requestsOptions());
		if ($this->document->status_code !== 200) {
			throw new Exception('Could not GET service document, run ServiceDocumentTest');
		}
		parent::__construct($name, $data, $dataName);
	}

	protected function requestsOptions() {
		$options = array(
			'useragent' => 'Gorilla/0.1 php-requests/' . Requests::VERSION
		);

		if (!empty($this->auth)) {
			$options = array(
				'auth' => array($this->auth['user'], $this->auth['pass'])
			);
		}
		return $options;
	}

	/**
	 * Test that the we have post collections
	 */
	public function testListPosts() {
		$available = self::getCollectionsFromDocument($this->document);
		$names = array();
		foreach ($available as $name => $collection) {
			if (in_array(AtomPubHelper::AtomEntryMediaType, $collection['accepted'])) {
				$names[] = $name;
			}
		}
		$this->assertNotEmpty($available);

		Gorilla::$runner->reportList(Gorilla_Runner::REPORT_INFO, 'Post collections found:', $names);

		$collection = $available[$names[0]];
		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Using ' . $collection['name'] . ' at ' . $collection['href']);

		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Checking current');
		$current = Requests::get($collection['href'], array(), array(), $this->requestsOptions());
		$this->assertEquals(200, $current->status_code, 'Could not retrieve current posts: ' . $current->body);

		$feed = new SimplePie();
		$feed->set_raw_data($current->body);
		$feed->set_stupidly_fast();
		$feed->init();

		$this->assertNull($feed->error(), 'Error reading feed of current: ' . $feed->error());

		$current = $feed->get_items();
		$ids = array();
		if (!empty($current)) {
			foreach ($current as &$item) {
				Gorilla::$runner->report(Gorilla_Runner::REPORT_DEBUG, 'Found post: ' . $item->get_title());
				$ids[] = $item->get_id();
			}
		}
		else {
			 Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'No posts found');
		}
	}

	/**
	 * Test creating a very sparse post
	 *
	 * Sets title, summary and id.
	 */
	public function testSparsePost() {
		$available = self::getCollectionsFromDocument($this->document);
		$proper = null;
		foreach ($available as $name => $collection) {
			if (in_array(AtomPubHelper::AtomEntryMediaType, $collection['accepted'])) {
				$proper = $collection;
				break;
			}
		}
		$this->assertNotNull($proper);

		$collection = $proper;

		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Testing basics');
		$entry = new AtomPubHelper_Entry();
		$headers = array(
			'Content-Type' => AtomPubHelper::AtomEntryMediaType
		);

		$slug_num = sprintf("%06d", rand(0, 100000));
		$slug = 'ape-' . $slug_num;
		$slug_re = 'ape.?' . $slug_num;
		$headers['Slug'] = $slug;

		$data = $entry->serialize_xml();
		Gorilla::$runner->report(Gorilla_Runner::REPORT_DEBUG, "Submitting entry:\n" . $data);

		$poster = Requests::post($collection['href'], $headers, $data, self::requestsOptions());
		$this->assertEquals(201, $poster->status_code, 'Could not create new post: ' . $poster->body);
		$this->assertNotEmpty($poster->headers['location']);

		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Posting of new entry reported success, location: ' . $poster->headers['location']);
	}

	/**
	 * Provide the paths of all XML files in data/posts/
	 *
	 * @return array
	 */
	public function examplePostProvider() {
		$files = glob(Gorilla::$path . '/lib/tests/data/posts/*.xml');
		foreach ($files as &$file) {
			$file = array($file);
		}
		return $files;
	}

	/**
	 * Test creating a very sparse post
	 *
	 * Only has the defaults, and nothing more
	 * @dataProvider examplePostProvider
	 * @param string $post Path to post template
	 */
	public function testPostFromTemplate($post) {
		// Grab the correct collection
		$available = self::getCollectionsFromDocument($this->document);
		$proper = null;
		foreach ($available as $name => $collection) {
			if (in_array(AtomPubHelper::AtomEntryMediaType, $collection['accepted'])) {
				$proper = $collection;
				break;
			}
		}
		$this->assertNotNull($proper);
		$collection = $proper;

		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Creating from ' . str_replace(Gorilla::$path, 'Gorilla', $post));

		// Get the template data
		$data = file_get_contents($post);

		// Load it up
		$entry = AtomPubHelper_Entry::from_template($data);

		// Give it a random ID
		$entry->set_element('id', 'tag:ryanmccue.info,2012:' . sprintf("%06d%06d", rand(0, 100000), rand(0, 100000)));
		$entry->set_element('updated', date('c'));

		$headers = array(
			'Content-Type' => AtomPubHelper::AtomEntryMediaType
		);

		// Generate a random slug (we'll check this later)
		$slug_num = sprintf("%06d", rand(0, 100000));
		$slug = 'ape-' . $slug_num;
		$slug_re = '#ape.?' . $slug_num . '#i';
		$headers['Slug'] = $slug;

		// Convert to XML
		$data = $entry->serialize_xml();
		Gorilla::$runner->report(Gorilla_Runner::REPORT_DEBUG, "Submitting entry:\n" . $data);

		// And then POST it to create it
		$poster = Requests::post($collection['href'], $headers, $data, self::requestsOptions());
		$this->assertEquals(201, $poster->status_code, 'Could not create new post: ' . $poster->body);
		$this->assertNotEmpty($poster->headers['location']);

		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Posting of new entry reported success, location: ' . $poster->headers['location']);

		// Check the data we got from the POST request
		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Examining the new entry as returned in the POST response');
		$wrapped = AtomPubHelper::wrap_entry($poster->body);
		$sp = new SimplePie();
		$sp->set_raw_data($wrapped);
		$sp->set_stupidly_fast();
		$sp->init();
		$this->assertNull($sp->error(), 'New entry is not well-formed: ' . $sp->error());

		$remote_entry = $sp->get_item(0);
		$this->checkEntry($entry, $remote_entry);

		$found = false;
		foreach ($remote_entry->get_links() as $link) {
			if (preg_match($slug_re, $link) > 0) {
				$found = true;
				break;
			}
		}
		if ($found) {
			Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Slug was used in server-generated URI');
		}
		else {
			Gorilla::$runner->report(Gorilla_Runner::REPORT_WARNING, 'Slug not used in server-generated URI');
		}

		// And now check at the new location!
		Gorilla::$runner->report(Gorilla_Runner::REPORT_INFO, 'Checking at the new location');
		$new = Requests::get($poster->headers['location'], array(), array(), self::requestsOptions());
		$this->assertEquals(200, $new->status_code, 'New post not found at location');

		$wrapped = AtomPubHelper::wrap_entry($new->body);
		$sp = new SimplePie();
		$sp->set_raw_data($wrapped);
		$sp->set_stupidly_fast();
		$sp->init();
		$this->assertNull($sp->error(), 'New entry is not well-formed: ' . $sp->error());

		$remote_entry = $sp->get_item(0);
		$this->checkEntry($entry, $remote_entry);
	}

	protected function checkEntry($entry, $remote_entry) {
		if ($entry->get_element('title') !== null) {
			$this->assertEquals($entry->get_element('title')->nodeValue, $remote_entry->get_title(), 'Titles not equal');
		}
		if ($entry->get_element('summary') !== null) {
			$this->assertEquals($entry->get_element('summary')->nodeValue, $remote_entry->get_summary(), 'Summaries not equal');
		}
		if ($entry->get_element('content') !== null) {
			$this->assertEquals($entry->get_element('content')->nodeValue, $remote_entry->get_content(), 'Content not equal');
		}

		// TODO: Check categories
		// TODO: Check dc:subject
	}

	protected static function getCollectionsFromDocument($document) {
		$reader = new SimpleXMLElement($document->body);
		$reader->registerXPathNamespace('app', 'http://www.w3.org/2007/app');
		$found_collections = $reader->xpath('//app:collection');
		$collections = array();
		foreach ($found_collections as $col) {
			$title = $col->children('http://www.w3.org/2005/Atom');
			$title = (string) $title->title;
			$accepted = array();
			// We need this because otherwise SimpleXML gets funky with >1 'accept'
			foreach ($col->accept as $accept) {
				$accepted[] = (string) $accept;
			}
			$collections[$title] = array('name' => $title, 'accepted' => $accepted, 'href' => (string) $col['href']);
		}

		return $collections;
	}
}