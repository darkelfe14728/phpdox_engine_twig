<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\fDOM\fDOMDocument;
use TheSeer\fDOM\fDOMException;
use TheSeer\phpDox\Collector\AbstractUnitObject;

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
            $this->dom->getDOMXPath()->registerNamespace('dox', AbstractUnitObject::XMLNS);
        }
        catch(fDOMException $e) {}
    }
}