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
     * @param DOMNodeList $list The DOM elements
     */
    public function __construct (DOMNodeList $list) {
        $this->list = $list;
        $this->xpath = null;

        parent::__construct($this->list);
    }
    /**
     * Create a new wrapper from an alone node (not a DOMNodeList)
     *
     * @param DOMNode $node The node
     *
     * @return XmlWrapper The new wrapper
     */
    public static function createFromNode (DOMNode $node): XmlWrapper {
        $dom = new DOMDocument();
        $dom->appendChild( $dom->importNode($node, true));

        return new XmlWrapper($dom->childNodes);
    }

    /**
     * Get the first element of internal list
     *
     * @return DOMElement|null The first element or null if list is empty
     */
    private function getFirstElement (): ?DOMElement {
        if ($this->list->length == 0)
            return null;

        /** @var DOMElement $element */$element = $this->list[0];
        if (is_null($this->xpath)) {
            $this->xpath = new DOMXPath($element->ownerDocument);
            $this->xpath->registerNamespace(AbstractObject::XML_PREFIX, AbstractUnitObject::XMLNS);
        }

        return $element;
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

        // Case of a name
        $element = $this->getFirstElement();
        if (is_null($element)) {
            return false;
        }

        // First, search for an attribute
        $sublist = $this->xpath->query('./@' . $nameOffset, $element);
        if ($sublist->length > 0) {
            return true;
        }

        // Then search a child
        $sublist = $this->xpath->query('./' . AbstractObject::XML_PREFIX . ':' . $nameOffset, $element);
        if ($sublist->length > 0) {
            return true;
        }

        return false;
    }
    /**
     * Get an element
     *
     * @param string|int $nameOffset The element name | The element offset
     *
     * @return bool Tru if the element exists, else False
     */
    public function elementGet ($nameOffset): ?XmlWrapper {
        // Case of an offset => get element from list
        if (is_int($nameOffset)) {
            return XmlWrapper::createFromNode($this->list[$nameOffset]);
        }

        // Case of a name
        $element = $this->getFirstElement();
        if (is_null($element)) {
            return null;
        }

        // First, search for an attribute
        $sublist = $this->xpath->query('./@' . $nameOffset, $element);
        if ($sublist->length > 0) {
            return new XmlWrapper($sublist);
        }

        // Then search a child
        $sublist = $this->xpath->query('./' . AbstractObject::XML_PREFIX . ':' . $nameOffset, $element);
        if ($sublist->length > 0) {
            return new XmlWrapper($sublist);
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