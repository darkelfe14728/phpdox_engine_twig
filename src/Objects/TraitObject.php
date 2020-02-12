<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\phpDox\Generator\TraitEndEvent;

/**
 * Object for traits
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
class TraitObject extends AbstractXmlObject {
    /**
     * Create a new object
     *
     * @param TraitEndEvent $event The trait event
     */
    public function __construct (TraitEndEvent $event) {
        parent::__construct($event->getTrait()->asDom());
    }

    /**
     * @inheritDoc
     */
    public function getVarName (): string {
        return 'interface';
    }
    /**
     * @inheritDoc
     */
    public function getObjectName (): string {
        return (string)$this->xml['full'];
    }
}