<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\phpDox\Generator\InterfaceEndEvent;

/**
 * Object for interfaces
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
class InterfaceObject extends AbstractXmlObject {
    /**
     * Create a new object
     *
     * @param InterfaceEndEvent $event The interface event
     */
    public function __construct (InterfaceEndEvent $event) {
        parent::__construct($event->getInterface()->asDom());
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