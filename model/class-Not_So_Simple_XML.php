<?php

/**
 * Extends SimpleXMLElement to write CDATA-Nodes and print a formated xml-string
 *
 * @author David Naber <kontakt@dnaber.de>
 * @version 2012.08.28
 * @url
 * @link http://coffeerings.posterous.com/php-simplexml-and-cdata
 */
class Not_So_Simple_XML extends SimpleXMLElement {

	/**
	 * adds a cdata node
	 *
	 * @param  string $cdata_text
	 * @return void
	 */
	public function add_cdata( $cdata_text ) {
		$node = dom_import_simplexml( $this );
		$no = $node->ownerDocument;
		$node->appendChild( $no->createCDATASection( $cdata_text ) );
	}

	/**
	 * returns a formatet xml string
	 *
	 * @return string
	 */
	public function as_formated_xml() {

		$xml_string = $this->asXML();
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$dom->loadXML( $xml_string );
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = TRUE;

		return $dom->saveXML();
	}
}
