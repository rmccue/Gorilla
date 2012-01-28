<?php

class AtomPubHelper_Entry {
	public $document;

	public function __construct($document = null) {
		class_exists('SimplePie');

		if ($document !== null) {
			$this->document = $document;
			return;
		}

		$this->document = new DOMDocument('1.0', 'utf-8');
		$this->set_element('title', 'From a Gorilla!');
		$this->set_element('summary', 'Summary from a <strong>&lt;Gorilla&gt;</strong> at ' . date('c'), array('type' => 'text'));
		$this->set_element('id', 'tag:ryanmccue.info,2012:' . sprintf("%06d%06d", rand(0, 100000), rand(0, 100000)));
	}

	public function get_element($name) {
		$xpath = new DOMXpath($this->document);
		$xpath->registerNamespace('atom', SIMPLEPIE_NAMESPACE_ATOM_10);
		$existing = $xpath->query('/atom:entry/atom:' . $name);
		if ($existing->length > 0) {
			return null;
		}

		return $existing->item(0);
	}

	public function set_element($name, $value = null, $attributes = array()) {
		$xpath = new DOMXpath($this->document);
		$xpath->registerNamespace('atom', SIMPLEPIE_NAMESPACE_ATOM_10);
		$existing = $xpath->query('/atom:entry/atom:' . $name);
		if ($existing->length > 0) {
			$existing = $existing->item(0);
		}
		else {
			$existing = $this->document->createElementNS(SIMPLEPIE_NAMESPACE_ATOM_10, $name);
			$this->document->firstChild->appendChild($existing);
		}

		if ($value !== null) {
			$existing->nodeValue = $value;
		}

		foreach ($attributes as $a_name => $a_value) {
			$existing->setAttributeNS(SIMPLEPIE_NAMESPACE_ATOM_10, $a_name, $a_value);
		}
	}

	public function serialize_xml() {
		return $this->document->saveXML();
	}

	public static function &from_template($string) {
		class_exists('SimplePie');

		$document = new DOMDocument('1.0', 'utf-8');
		$document->loadXML($string);
		$entry = new AtomPubHelper_Entry($document);
		return $entry;
	}
}