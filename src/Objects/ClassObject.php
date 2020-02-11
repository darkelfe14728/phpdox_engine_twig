<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

use TheSeer\phpDox\Generator\ClassEndEvent;
use TheSeer\phpDox\Generator\Engine\TwigEngine;

class ClassObject extends AbstractObject {
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
        return $this->dom->queryOne('/' . TwigEngine::XML_PREFIX_PHPDOC . ':class/@full')->textContent;
    }
}