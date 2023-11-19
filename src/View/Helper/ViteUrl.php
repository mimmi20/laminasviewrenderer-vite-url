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
use function is_string;
use function json_decode;
use function reset;
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

    /**
     * Outputs message depending on flag
     *
     * @throws void
     */
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

    /**
     * @param array<string, mixed>    $params
     * @param iterable<string, mixed> $options
     *
     * @throws RuntimeException
     */
    public function js(
        string | null $name = null,
        array $params = [],
        iterable $options = [],
        bool $reuseMatchedParams = false,
    ): string {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        $view = $this->getView();

        if (!$view instanceof PhpRenderer) {
            throw new RuntimeException('A PHP View Renderer is required');
        }

        $server = $this->hotServer();

        if ($server) {
            return $view->url($server . '/' . $name, $params, $options, $reuseMatchedParams);
        }

        try {
            $manifest = $this->manifestContents();
        } catch (JsonException $e) {
            throw new RuntimeException('Could not load manifest file', 0, $e);
        }

        if (!isset($manifest[$name]['file'])) {
            throw new RuntimeException('Unknown Vite entrypoint ' . $name);
        }

        return $view->url(
            $this->buildDir . '/' . $manifest[$name]['file'],
            $params,
            $options,
            $reuseMatchedParams,
        );

//        $vm = new Manifest($this->publicDir . '/' . $this->buildDir . '/manifest.json', $view->url('/'));
//
//        $entrypoint = $vm->getEntrypoint($name);
//
//        if (!$entrypoint || $entrypoint === []) {
//            throw new RuntimeException('Unknown Vite entrypoint ' . $name);
//        }
//
//        ["url" => $url, "hash" => $hash] = $entrypoint;
//
//        $view->revisionInlineScript()->appendFile($url, 'module', ['crossorigin' => 'crossorigin', 'integrity' => $hash], addRevision: false);
//
//        foreach ($vm->getImports($name, false) as $import) {
//            ["url" => $url] = $import;
//            $item = new \stdClass();
//            $item->rel = 'modulepreload';
//            $item->href = $url;
//            $item->crossorigin = ''
//            echo "<link rel='modulepreload' href='$url' />" . PHP_EOL;
//        }
    }

    /**
     * @param array<string, mixed>    $params
     * @param iterable<string, mixed> $options
     *
     * @throws RuntimeException
     */
    public function css(
        string | null $name = null,
        array $params = [],
        iterable $options = [],
        bool $reuseMatchedParams = false,
    ): string {
        if ($this->publicDir === null) {
            throw new RuntimeException('A Public Dir is required');
        }

        $view = $this->getView();

        if (!$view instanceof PhpRenderer) {
            throw new RuntimeException('A PHP View Renderer is required');
        }

        $server = $this->hotServer();

        if ($server) {
            return $view->url($server . '/' . $name, $params, $options, $reuseMatchedParams);
        }

        try {
            $manifest = $this->manifestContents();
        } catch (JsonException $e) {
            throw new RuntimeException('Could not load manifest file', 0, $e);
        }

        if (!isset($manifest[$name]['css'])) {
            throw new RuntimeException('Unknown Vite CSS entrypoint ' . $name);
        }

        $firstFile = reset($manifest[$name]['css']);

        if (!is_string($firstFile)) {
            throw new RuntimeException('Unknown Vite CSS entrypoint ' . $name);
        }

        return $view->url($this->buildDir . '/' . $firstFile, $params, $options, $reuseMatchedParams);
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
     * @throws JsonException
     */
    private function manifestContents(): array
    {
        if ($this->buildDir === null) {
            return [];
        }

        $manifestPath = $this->publicDir . '/' . $this->buildDir . '/manifest.json';

        if (!is_file($manifestPath)) {
            throw new RuntimeException(sprintf('Vite manifest not found at: %s', $manifestPath));
        }

        $content = file_get_contents($manifestPath);

        if (!$content) {
            throw new RuntimeException(
                sprintf('coule not read and decode Vite manifest at: %s', $manifestPath),
            );
        }

        return json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
