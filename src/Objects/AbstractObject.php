<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMException;
use TheSeer\phpDox\Collector\AbstractUnitObject;
use TheSeer\phpDox\Generator\Engine\TwigEngine;

abstract class AbstractObject implements IObject {
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
            $this->dom->getDOMXPath()->registerNamespace(TwigEngine::XML_PREFIX_PHPDOC, AbstractUnitObject::XMLNS);
        }
        catch(fDOMException $e) {}
    }

    /**
     * @inheritDoc
     */
    public function getObjectValue (): ?XmlWrapper {
        return new XmlWrapper($this->dom->query('/' . TwigEngine::XML_PREFIX_PHPDOC . ':' . $this->getVarName()));
    }
}