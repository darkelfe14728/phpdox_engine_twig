<?php

namespace TheSeer\phpDox\Generator\Engine;

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
     * Default: template/twig
     *
     * @return string Template directory path
     */
    public function getTemplateDirectory (): string {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getHomeDirectory()->getPathname() . '/templates/twig';
        $node = $this->ctx->queryOne('cfg:template');

        if (!$node) {
            return $default;
        }

        if ($node->hasAttribute('path')) {
            return $node->getAttribute('path', $default);
        }

        return $node->getAttribute('dir', $default);
    }

    /**
     * Get compilation cache directory
     *
     * Default: template/twig/cache
     *
     * @return string Template's resources directory path
     */
    public function getCacheDirectory (): string {
        $default = $this->getGeneratorConfig()->getProjectConfig()->getWorkDirectory()->getPathname() . '/twig';
        $node = $this->ctx->queryOne('cfg:cache');

        if (!$node) {
            return $default;
        }

        return $node->getAttribute('path', $default);
    }

    /**
     * Get resources directory
     *
     * Default: template/twig/resources
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