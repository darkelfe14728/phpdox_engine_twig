<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\phpDox\Generator\Engine\TwigEngine;

/**
 * Object for XML-based elements
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
abstract class AbstractXmlObject implements IObject {
    /**
     * @var XmlWrapper The DOM structure
     */
    protected $xml;

    /**
     * Create a new object
     *
     * @param fDOMDocument $dom The DOM structure
     */
    public function __construct (fDOMDocument $dom) {
        $this->xml = XmlWrapper::createFromNode($dom->documentElement);
    }

    /**
     * @inheritDoc
     */
    public function getObjectValue (): ?XmlWrapper {
        return $this->xml->xpathGet('/' . TwigEngine::XML_PREFIX_PHPDOC . ':' . $this->getVarName());
    }
}