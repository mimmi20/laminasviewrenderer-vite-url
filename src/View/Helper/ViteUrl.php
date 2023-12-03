<?php
/**
 * This file is part of the mimmi20/laminasviewrenderer-vite-url package.
 *
 * Copyright (c) 2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\ViteUrl\View\Helper;

use JsonException;
use Laminas\View\Exception\RuntimeException;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;

use function file_get_contents;
use function is_file;
use function json_decode;
use function rtrim;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ViteUrl extends AbstractHelper
{
    /** @throws void */
    public function __construct(private readonly string | null $publicDir, private readonly string | null $buildDir)
    {
        // nothing to do
    }

    /** @throws void */
    public function __invoke(): self
    {
        return $this;
    }

    /** @throws void */
    public function getPublicDir(): string | null
    {
        return $this->publicDir;
    }

    /** @throws void */
    public function getBuildDir(): string | null
    {
        return $this->buildDir;
    }

    /** @throws RuntimeException */
    public function js(string $name): string
    {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        $view = $this->getView();

        if (!$view instanceof PhpRenderer) {
            throw new RuntimeException('A PHP View Renderer is required');
        }

        $server = $this->hotServer();

        if ($server) {
            return $server . '/' . $name;
        }

        $manifest = $this->manifestContents();

        if (!isset($manifest[$name]['file'])) {
            throw new RuntimeException('Unknown Vite JS entrypoint ' . $name);
        }

        return $view->serverUrl('/' . $this->buildDir . '/' . $manifest[$name]['file']);
    }

    /** @throws RuntimeException */
    public function css(string $name): string
    {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        $view = $this->getView();

        if (!$view instanceof PhpRenderer) {
            throw new RuntimeException('A PHP View Renderer is required');
        }

        $server = $this->hotServer();

        if ($server) {
            return $server . '/' . $name;
        }

        $manifest = $this->manifestContents();

        if (!isset($manifest[$name]['file'])) {
            throw new RuntimeException('Unknown Vite CSS entrypoint ' . $name);
        }

        return $view->serverUrl('/' . $this->buildDir . '/' . $manifest[$name]['file']);
    }

    /** @throws void */
    private function hotServer(): string | null
    {
        if (!is_file($this->publicDir . '/hot')) {
            return null;
        }

        $content = file_get_contents($this->publicDir . '/hot');

        if (!$content) {
            return null;
        }

        return rtrim($content);
    }

    /**
     * Retrieve our manifest file contents.
     *
     * @return array<string, array{file: string, imports: array<string, mixed>, css: array<string, mixed>}>
     *
     * @throws RuntimeException
     */
    private function manifestContents(): array
    {
        if ($this->buildDir === null) {
            throw new RuntimeException('A Build Dir is required');
        }

        $manifestPath = $this->publicDir . '/' . $this->buildDir . '/manifest.json';

        if (!is_file($manifestPath)) {
            throw new RuntimeException(sprintf('Vite manifest not found at: %s', $manifestPath));
        }

        $content = file_get_contents($manifestPath);

        if (!$content) {
            throw new RuntimeException(
                sprintf('Could not read and decode Vite manifest at: %s', $manifestPath),
            );
        }

        try {
            return json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException(
                sprintf('Could not decode Vite manifest at: %s', $manifestPath),
                0,
                $e,
            );
        }
    }
}
