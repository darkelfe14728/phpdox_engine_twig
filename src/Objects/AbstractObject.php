<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMException;
use TheSeer\phpDox\Collector\AbstractUnitObject;

abstract class AbstractObject implements IObject {
    /**
     * @var string The XML namespace prefix for phpDox
     */
    public const XML_PREFIX = 'dox';

    /**
     * @var fDOMDocument The DOM structure
     */
    protected $dom;

    /**
     * Create a new object
     *
     * @param fDOMDocument $dom The DOM structure
     */
    public function __construct (fDOMDocument $dom) {
        $this->dom = $dom;
        try {
            $this->dom->getDOMXPath()->registerNamespace(self::XML_PREFIX, AbstractUnitObject::XMLNS);
        }
        catch(fDOMException $e) {}
    }

    /**
     * @inheritDoc
     */
    public function getObjectValue (): ?XmlWrapper {
        return new XmlWrapper($this->dom->query('/' . self::XML_PREFIX . ':' . $this->getVarName()));
    }
}