<?php

/**
 * This file is part of the mimmi20/laminasviewrenderer-vite-url package.
 *
 * Copyright (c) 2023-2026, Thomas Mueller <mimmi20@live.de>
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
use function mb_ltrim;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class ViteUrl extends AbstractHelper
{
    /** @throws void */
    public function __construct(
        private readonly string | null $publicDir,
        private readonly string | null $buildDir,
        private readonly string | null $viteHost = null,
    ) {
        // nothing to do
    }

    /** @throws void */
    public function __invoke(): self
    {
        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getPublicDir(): string | null
    {
        return $this->publicDir;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getBuildDir(): string | null
    {
        return $this->buildDir;
    }

    /**
     * @throws RuntimeException
     *
     * @api
     */
    public function file(string $name): string
    {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        if ($this->viteHost) {
            return $this->viteHost . '/' . mb_ltrim($name, '/');
        }

        $view = $this->getView();

        if (!$view instanceof PhpRenderer) {
            throw new RuntimeException('A PHP View Renderer is required');
        }

        $manifest = $this->manifestContents();

        if (!isset($manifest[$name]['file'])) {
            throw new RuntimeException('Unknown Vite entrypoint ' . $name);
        }

        return $view->serverUrl('/' . $this->buildDir . '/' . $manifest[$name]['file']);
    }

    /**
     * @throws void
     *
     * @api
     */
    public function isDev(): bool
    {
        return !empty($this->viteHost);
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

        $manifestPathV4 = $this->publicDir . '/' . $this->buildDir . '/manifest.json';
        $manifestPathV5 = $this->publicDir . '/' . $this->buildDir . '/.vite/manifest.json';

        if (is_file($manifestPathV5)) {
            $manifestPath = $manifestPathV5;
        } elseif (is_file($manifestPathV4)) {
            $manifestPath = $manifestPathV4;
        } else {
            throw new RuntimeException(
                sprintf('Vite manifest not found at %s or at %s', $manifestPathV4, $manifestPathV5),
            );
        }

        $content = file_get_contents($manifestPath);

        if (!$content) {
            throw new RuntimeException(
                sprintf('Could not read Vite manifest at: %s', $manifestPath),
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
