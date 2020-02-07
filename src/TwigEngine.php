<?php

namespace TheSeer\phpDox\Generator\Engine;

use TheSeer\phpDox\Generator\Engine\Objects\IObject;
use TheSeer\phpDox\Generator\PHPDoxEndEvent;
use TheSeer\phpDox\Generator\PHPDoxStartEvent;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * The Twig engine
 *
 * @package TheSeer\phpDox\Generator\Engine
 */
class TwigEngine implements EngineInterface {
    /**
     * @var TwigEngineConfig The engine configuration
     */
    private $config;

    /**
     * @var Environment The Twig main variable (environment)
     */
    private $twig;

    /**
     * Instanciate engine
     *
     * @param TwigEngineConfig $config The engine configuration
     */
    public function __construct (TwigEngineConfig $config) {
        $this->config = $config;
    }
    /**
     * Register phpDox event handlers
     *
     * @param EventHandlerRegistry $registry
     *
     * @throws EventHandlerRegistryException When handler registration failed
     */
    public function registerEventHandlers (EventHandlerRegistry $registry): void {
        $registry->addHandler('phpdox.start', $this, 'start');
        $registry->addHandler('phpdox.end', $this, 'finish');
    }

    /**
     * When build start
     *
     * @param PHPDoxStartEvent $event The start event
     */
    public function start (/** @noinspection PhpUnusedParameterInspection */PHPDoxStartEvent $event): void {
        $loader = new FilesystemLoader($this->config->getTemplateDirectory());
        $this->twig = new Environment(
            $loader,
            [
                'cache' => $this->config->getCacheDirectory(),
            ]
        );
    }
    /**
     * When build finish
     *
     * @param PHPDoxEndEvent $event The end event
     */
    public function finish (PHPDoxEndEvent $event): void {
        /// TODO copy 'resources' directory
    }

    /**
     * Render a template about an object
     *
     * @param string  $template The relative path of the template
     * @param string  $outputFilename The relative output filename (without extension)
     * @param IObject $object The object to render
     *
     * @throws LoaderError When template load failed
     * @throws RuntimeError When render failed
     * @throws SyntaxError When template syntax has errors
     */
    private function render (string $template, string $outputFilename, IObject $object): void {
        $object_className = get_class($object);
        $object_name = strtolower(substr($object_className, 0, strlen($object_className) - strlen('Object')));

        $tpl = $this->twig->load($this->config->getTemplateDirectory() . $template);
        $output = $tpl->render(
            [
                $object_name => $object
            ]
        );

        file_put_contents( $this->config->getOutputDirectory() . $outputFilename . $this->config->getFileExtension(), $output);
    }
}