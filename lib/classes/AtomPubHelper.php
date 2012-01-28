<?php

class AtomPubHelper {
	const AtomEntryMediaType = 'application/atom+xml;type=entry';

	/**
	 * Wraps an entry in <atom:feed> elements so that SimplePie can read it
	 *
	 * @param string $body XML document
	 * @return string New XML document
	 */
	public static function wrap_entry($body) {
		$document = new DOMDocument('1.0', 'utf-8');
		$document->loadXML($body);
		$newroot = $document->createElementNS(SIMPLEPIE_NAMESPACE_ATOM_10, 'feed');
		$newroot->appendChild($document->firstChild);
		$document->appendChild($newroot);
		return $document->saveXML();
	}
}