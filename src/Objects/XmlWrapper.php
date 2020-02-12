<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use ArrayAccess;
use Countable;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Error;
use IteratorIterator;
use TheSeer\phpDox\Collector\AbstractUnitObject;
use TheSeer\phpDox\Generator\Engine\TwigEngine;

/**
 * A Twig wrapper for XML
 *
 * Allow to access XML structure using twig object
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
class XmlWrapper extends IteratorIterator implements Countable, ArrayAccess {
    /**
     * @var DOMNodeList The DOM elements
     */
    private $list;
    /**
     * @var DOMXPath|null The current XPath (null is not set yet)
     */
    private $xpath;

    /**
     * Create a new wrapper
     *
     * @param DOMNodeList $list  The DOM elements
     * @param string      $nsUrl The URL corresponding to {@see TwigEngine::XML_PREFIX_PHPDOC} prefix
     */
    public function __construct (DOMNodeList $list, string $nsUrl = AbstractUnitObject::XMLNS) {
        $this->list = $list;

        if ($this->list->length > 0) {
            /** @var DOMElement $element */$element = $this->list[0];

            $this->xpath = new DOMXPath($element->ownerDocument);
            $this->xpath->registerNamespace(TwigEngine::XML_PREFIX_PHPDOC, $nsUrl);
        }

        parent::__construct($this->list);
    }
    /**
     * Create a new wrapper from an alone node (not a DOMNodeList)
     *
     * @param DOMNode $node The node
     * @param string      $nsUrl The URL corresponding to {@see TwigEngine::XML_PREFIX_PHPDOC} prefix
     *
     * @return XmlWrapper The new wrapper
     */
    public static function createFromNode (DOMNode $node, string $nsUrl = AbstractUnitObject::XMLNS): XmlWrapper {
        $dom = new DOMDocument();
        $dom->appendChild( $dom->importNode($node, true));

        return new XmlWrapper($dom->childNodes, $nsUrl);
    }

    /**
     * The first element of list, directly as DOMElement
     *
     * @return DOMElement|null The first element
     */
    public function asRawNode(): ?DOMElement {
        if ($this->list->length == 0)
            return null;

        return $this->list[0];
    }
    /**
     * The list, directly as DOMNodeList
     *
     * @return DOMNodeList The list
     */
    public function asRawList(): DOMNodeList {
        return $this->list;
    }
    /**
     * Return first element as XML
     *
     * @return string The XML
     */
    public function asXml (): string {
        $element = $this->asRawNode();
        if (is_null($element))
            return '';

        return $element->ownerDocument->saveXML($element);
    }

    /**
     * Check if a XPath element exists
     *
     * @param string $xpath The XPath query
     *
     * @return bool True if the XPath query as returned something
     */
    public function xpathExists (string $xpath): bool {
        $element = $this->asRawNode();
        if (is_null($element)) {
            return false;
        }

        if (substr($xpath, 0, 2) != './') {
            $xpath = './' . $xpath;
        }

        $sublist = $this->xpath->query($xpath, $element);
        if ($sublist->length > 0) {
            return true;
        }

        return false;
    }
    /**
     * Get a XPath element
     *
     * @param string $xpath The XPath query
     *
     * @return XmlWrapper|null The elements corresponding to XPath query or Null if nothing found
     */
    public function xpathGet (string $xpath): ?XmlWrapper {
        $element = $this->asRawNode();
        if (is_null($element)) {
            return null;
        }

        if (substr($xpath, 0, 2) != './') {
            $xpath = './' . $xpath;
        }

        $sublist = $this->xpath->query($xpath, $element);
        if ($sublist->length > 0) {
            return new XmlWrapper($sublist);
        }

        return null;
    }

    /**
     * Check if an element exists
     *
     * @param string|int $nameOffset The element name | The element offset
     *
     * @return bool Tru if the element exists, else False
     */
    public function elementExists ($nameOffset): bool {
        // Case of an offset => check if element exists in list
        if (is_int($nameOffset)) {
            return isset($this->list[$nameOffset]);
        }

        return $this->xpathExists('./@' . $nameOffset)                                              // First, search for an attribute
               || $this->xpathExists('./' . TwigEngine::XML_PREFIX_PHPDOC . ':' . $nameOffset)      // Then search a child (with PHPDox prefix)
               || $this->xpathExists('./' . $nameOffset);                                           // Then search a child (without prefix)
    }
    /**
     * Get an element
     *
     * @param string|int $nameOffset The element name | The element offset
     *
     * @return XmlWrapper|null The element
     */
    public function elementGet ($nameOffset): ?XmlWrapper {
        // Case of an offset => get element from list
        if (is_int($nameOffset)) {
            return XmlWrapper::createFromNode($this->list[$nameOffset]);
        }

        // First, search for an attribute
        if (!is_null($out = $this->xpathGet('./@' . $nameOffset))) {
            return $out;
        }
        // Then search a child (with PHPDox prefix)
        if (!is_null($out = $this->xpathGet('./' . TwigEngine::XML_PREFIX_PHPDOC . ':' . $nameOffset))) {
            return $out;
        }
        // Then search a child (without prefix)
        if (!is_null($out = $this->xpathGet('./' . $nameOffset))) {
            return $out;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function count (): int {
        return $this->list->count();
    }

    /**
     * @inheritDoc
     */
    public function current (): XmlWrapper {
        return XmlWrapper::createFromNode(parent::current());
    }

    /**
     * @inheritDoc
     */
    public function offsetExists ($offset): bool {
        return $this->elementExists($offset);
    }
    /**
     * @inheritDoc
     */
    public function offsetGet ($offset): ?XmlWrapper {
        return $this->elementGet($offset);
    }
    /**
     * @inheritDoc
     */
    public function offsetSet ($offset, $value) {
        throw new Error('XmlWrapper elements can\'t be set');
    }
    /**
     * @inheritDoc
     */
    public function offsetUnset ($offset) {
        throw new Error('XmlWrapper elements can\'t be deleted');
    }

    /**
     * Check if an attribute / child exists
     *
     * @param string $name The attribute / child name
     *
     * @return bool True if the attribue / child exists, else False
     */
    public function __isset (string $name): bool {
        return $this->elementExists($name);
    }
    /**
     * Get value of an attribute / child
     *
     * @param string $name The attribute / child name
     *
     * @return XmlWrapper|null The attribue / child value
     */
    public function __get (string $name): ?XmlWrapper {
        return $this->elementGet($name);
    }

    /**
     * Convert to string
     *
     * @return string The text content of current element
     */
    public function __toString (): string {
        if ($this->list->length == 0) {
            return '';
        }

        return $this->list[0]->textContent;
    }
}