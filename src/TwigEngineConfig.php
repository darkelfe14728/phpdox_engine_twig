<?php

namespace TheSeer\phpDox\Generator\Engine;

use Monolog\Logger;
use TheSeer\phpDox\BuildConfig;

/**
 * Config about Twig engine
 *
 * @package TheSeer\phpDox\Generator\Engine
 */
class TwigEngineConfig extends BuildConfig {
    /**
     * Get template directory
     *
     * Default: <output_dir>/template/twig
     *
     * @return string Template directory path
     */
    public function getTemplateDirectory (): string {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getHomeDirectory()->getPathname() . '/templates/twig';
        $node = $this->ctx->queryOne('cfg:template');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }
    /**
     * Get resources directory
     *
     * Default: <output_dir>/template/twig/resources
     *
     * @return string Template's resources directory path
     */
    public function getResourceDirectory (): string {
        $default = $this->getTemplateDirectory() . '/resources';
        $node = $this->ctx->queryOne('cfg:resource');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }
    /**
     * Get build cache directory
     *
     * Default: <working_dir>/twig/cache
     *
     * @return string Template's resources directory path
     */
    public function getCacheDirectory (): string {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getWorkDirectory()->getPathname() . '/twig/cache';
        $node = $this->ctx->queryOne('cfg:cache');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }

    /**
     * Get build log file
     *
     * Default: <working_dir>/twig/build.log
     *
     * @return string Build log file path
     */
    public function getLogFile (): string {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getWorkDirectory()->getPathname() . '/twig/build.log';
        $node = $this->ctx->queryOne('cfg:log');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }
    /**
     * Get build log minimum level
     *
     * Default: warning
     *
     * @return string|int Build log minimum level
     */
    public function getLogLevel () {
        $default = Logger::WARNING;
        $node = $this->ctx->queryOne('cfg:log');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('level', $default);
    }

    /**
     * Get output files extension
     *
     * Default: html
     *
     * @return string File extension
     */
    public function getFileExtension (): string {
        $res = $this->ctx->queryOne('cfg:file/@extension');
        return $res === null ? 'html' : $res->nodeValue;
    }
}