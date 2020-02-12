<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\phpDox\Generator\ClassEndEvent;

/**
 * Object for classes
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
class ClassObject extends AbstractXmlObject {
    /**
     * Create a new object
     *
     * @param ClassEndEvent $event The class event
     */
    public function __construct (ClassEndEvent $event) {
        parent::__construct($event->getClass()->asDom());
    }

    /**
     * @inheritDoc
     */
    public function getVarName (): string {
        return 'class';
    }
    /**
     * @inheritDoc
     */
    public function getObjectName (): string {
        return (string)$this->xml['full'];
    }
}