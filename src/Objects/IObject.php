<?php

namespace TheSeer\phpDox\Generator\Engine\Objects;

/**
 * Interface for all objects
 *
 * @package TheSeer\phpDox\Generator\Engine\Objects
 */
interface IObject {
    /**
     * The variable name in Twig
     *
     * @return string The Twig variable name
     */
    public function getVarName(): string;
    /**
     * The object name (i.e. class name, interface name, ...)
     *
     * Used to generate output filename
     *
     * @return string The object name
     */
    public function getObjectName(): string;
    /**
     * The object value
     *
     * @return mixed|null The object value
     */
    public function getObjectValue();
}