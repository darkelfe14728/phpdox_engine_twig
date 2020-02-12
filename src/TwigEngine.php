<?php

namespace TheSeer\phpDox\Generator\Engine;

use DirectoryIterator;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TheSeer\phpDox\Collector\AbstractUnitObject;
use TheSeer\phpDox\ConfigLoader;
use TheSeer\phpDox\Generator\ClassEndEvent;
use TheSeer\phpDox\Generator\Engine\Objects\ClassObject;
use TheSeer\phpDox\Generator\Engine\Objects\IObject;
use TheSeer\phpDox\Generator\Engine\Objects\XmlWrapper;
use TheSeer\phpDox\Generator\PHPDoxEndEvent;
use TheSeer\phpDox\Generator\PHPDoxStartEvent;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\EscaperExtension;
use Twig\Loader\FilesystemLoader;

/**
 * The Twig engine
 *
 * @package TheSeer\phpDox\Generator\Engine
 */
class TwigEngine implements EngineInterface {
    /**
     * @var string The XML namespace prefix for {@see AbstractUnitObject::XMLNS phpDox src}
     */
    public const XML_PREFIX_PHPDOC = 'phpdox';

    /**
     * @var TwigEngineConfig The engine configuration
     */
    private $config;
    /**
     * @var Logger The logger
     */
    private $logger;
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
     * Create the logger
     *
     * @param string $logFile The log file
     */
    private function createLogger (string $logFile): void {
        $formatter = new LineFormatter("[%datetime%] [%channel%] %level_name%: %message% %context% %extra%\n", 'Y-m-d H:i:s');
        $formatter->ignoreEmptyContextAndExtra(true);

        $handler = new StreamHandler($logFile, $this->config->getLogLevel());
        $handler->setFormatter($formatter);

        $this->logger = new Logger('twig');
        $this->logger->pushHandler($handler);
    }
    /**
     * Escape an object name (FQDN)
     *
     * @param string $objectName The object name
     *
     * @return string The escaped name
     */
    public static function escapeObjectName (string $objectName): string {
        return preg_replace('@[/\\\\:]@i', '_', $objectName);
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

        $registry->addHandler('class.end', $this, 'renderClass');
    }

    /**
     * When build start
     *
     * @param PHPDoxStartEvent $event The start event
     */
    public function start (PHPDoxStartEvent $event): void {
        $logFile = $this->config->getLogFile();
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $this->createLogger($logFile);
        $this->logger->debug('Build start');

        $cache = $this->config->getCacheDirectory();
        if (empty($cache)) {
            $cache = false;         // If cache path is explicitly empty, then disable cache
        }
        $this->logger->debug('Twig cache directory : ' . ($cache === false ? '<no cache>' : $cache));

        $loader = new FilesystemLoader($this->config->getTemplateDirectory());
        $this->twig = new Environment(
            $loader,
            [
                'cache' => $cache,
            ]
        );
        $this->logger->debug('Twig is ready');

        $this->twig->getExtension(EscaperExtension::class)->setEscaper('id',
            function (/** @noinspection PhpUnusedParameterInspection */ Environment $env, string $string, /** @noinspection PhpUnusedParameterInspection */ string $charset): string {
                return self::escapeObjectName($string);
            }
        );

        $this->twig->addGlobal('XML_PREFIX_PHPDOC', self::XML_PREFIX_PHPDOC);
        $this->twig->addGlobal('FILE_EXTENSION', $this->config->getFileExtension());

        $this->twig->addGlobal('project', XmlWrapper::createFromNode($this->config->getProjectNode(), ConfigLoader::XMLNS));
        $this->twig->addGlobal('index', XmlWrapper::createFromNode($event->getIndex()->asDom()->documentElement));
        $this->twig->addGlobal('source_tree', XmlWrapper::createFromNode($event->getTree()->asDom()->documentElement));

        $this->logger->debug('Twig global variables added');
    }
    /**
     * When build finish
     *
     * @param PHPDoxEndEvent $event The end event
     */
    public function finish (/** @noinspection PhpUnusedParameterInspection */ PHPDoxEndEvent $event): void {
        $this->logger->debug('Render index');
        $this->render('index', null, null);

        $this->logger->debug('Render namespace list');
        $this->render('namespaces', null, null);

        $this->logger->debug('Render interface list');
        $this->render('interfaces', null, null);

        $this->logger->debug('Render class list');
        $this->render('classes', null, null);

        $this->logger->debug('Render trait list');
        $this->render('traits', null, null);

        $resourcesDir = $this->config->getResourceDirectory();
        if (!empty($resourcesDir)) {
            $this->logger->debug('Copy resources directory content : ' . $resourcesDir);
            if (is_dir($resourcesDir)) {
                $resourcesDirLength = mb_strlen($resourcesDir);
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourcesDir));

                /** @var DirectoryIterator $entry */
                foreach ($iterator as $entry) {
                    if ($entry->isDir() && ($entry->getFilename() == '.' || $entry->getFilename() == '..')) {
                        continue;
                    }

                    $target = $this->config->getOutputDirectory() . mb_substr($entry->getPathname(), $resourcesDirLength);
                    $targetDir = dirname($target);

                    if (!is_dir($targetDir)) {
                        $this->logger->info('Directory "' . $targetDir . '" doesn\'t exist : create');
                        if (!mkdir($targetDir, 0775, true)) {
                            $this->logger->error('Unable to create directory "' . $targetDir . "'");
                            continue;
                        }
                    }
                    if (!copy($entry->getPathname(), $target)) {
                        $this->logger->error('Failed to copy "' . $entry->getPathname() . '" to "' . $target . '"');
                        continue;
                    }
                }
            }
            else {
                $this->logger->warning('  Invalid directory : ' & $resourcesDir);
            }
        }

        $this->logger->debug('Close Twig');
        unset($this->twig);

        $this->logger->debug('Build finished');
        $this->logger->close();
        unset($this->logger);
    }

    /**
     * Render a template about an object
     *
     * @param string       $templateName       The relative path of the template (without extension)
     * @param string|null  $outputSubdirectory The subdirectory for output path (Null if none)
     * @param IObject|null $object             The object to render (Null id none)
     *
     * @return bool True if render succeed, else False
     */
    private function render (string $templateName, ?string $outputSubdirectory, ?IObject $object): bool {
        $templateFile = $templateName . '.' . $this->config->getFileExtension() . '.twig';
        $outputDir = $this->config->getOutputDirectory() . (empty($outputSubdirectory) ? '' : '/' . $outputSubdirectory) . '/';
        $outputFile = $outputDir
                      . (is_null($object) ? basename($templateName) : self::escapeObjectName($object->getObjectName()))
                      . '.' . $this->config->getFileExtension();

        if (!is_dir($outputDir)) {
            $this->logger->info('Output directory "' . $outputDir . '" is missing : create');
            if (!mkdir($outputDir, 0755, true)) {
                $this->logger->error('Failed to create output directory "' . $outputDir . '"');
                return false;
            }
        }

        $context = [];
        if (!is_null($object)) {
            $context[$object->getVarName()] = $object->getObjectValue();
        }

        $this->logger->debug('Load template : ' . $templateFile);
        try {
            $tpl = $this->twig->load($templateFile);
            $output = $tpl->render($context);
        }
        catch (Error $e) {
            $this->logger->error('Failed to render using Twig : ' . $e->getMessage(), ['exception' => $e]);
            return false;
        }

        $this->logger->debug('Output file : ' . $outputFile);
        if (file_put_contents($outputFile, $output) === false) {
            $this->logger->error('Failed to write output file : ' . $outputFile);
            return false;
        }
        return true;
    }
    /**
     * Render a class
     *
     * @param ClassEndEvent $event The event
     */
    public function renderClass (ClassEndEvent $event): void {
        $class = new ClassObject($event);

        $this->logger->debug('Render class ' . $class->getObjectName());
        if (!$this->render('class', 'classes', $class)) {
            return;
        }
    }
}
