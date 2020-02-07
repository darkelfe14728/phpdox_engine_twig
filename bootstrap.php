<?php

namespace TheSeer\phpDox\Generator\Engine;

use TheSeer\phpDox\BootstrapApi;

/**
 * @var BootstrapApi $phpDox phpDox variable used to register engines
 */
$phpDox->registerEngine('twig', 'Build an output using Twig templates')
    ->implementedByClass('TheSeer\\phpDox\\Generator\\Engine\\TwigEngine')
    ->withConfigClass('TheSeer\\phpDox\\Generator\\Engine\\TwigEngineConfig');
