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

use Laminas\View\Exception\RuntimeException;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function json_encode;
use function sprintf;

final class ViteUrlTest extends TestCase
{
    /** @throws Exception */
    public function testInvoke(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        self::assertSame($object, $object());
    }

    /** @throws RuntimeException */
    public function testJsWithoutPublicDir(): void
    {
        $publicDir = null;
        $buildDir  = null;

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Public Dir is required');

        $object->js();
    }

    /** @throws RuntimeException */
    public function testJsWithoutRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->js();
    }

    /** @throws RuntimeException */
    public function testJsWithWrongRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(RendererInterface::class);

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->js();
    }

    /** @throws RuntimeException */
    public function testJsWithHotRelaoding(): void
    {
        $root    = vfsStream::setup('root');
        $hotDir  = 'test-hot-dir';
        $name    = 'test.js';
        $urlName = 'test2.js';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent($hotDir);

        $root->addChild($file1);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())
            ->method('__call')
            ->with('url', [$hotDir . '/' . $name, [], [], false])
            ->willReturn($hotDir . '/' . $urlName);

        $object->setView($view);

        self::assertSame($hotDir . '/' . $urlName, $object->js($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testJsWithoutManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $dir = vfsStream::newDirectory($buildDir);

        $root->addChild($dir);

        $publicDir    = 'test-public-dir';
        $manifestPath = $publicDir . '/' . $buildDir . '/manifest.json';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Vite manifest not found at: %s', $manifestPath));

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testJsWithManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(sprintf('Unknown Vite entrypoint %s', $name));

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     */
    public function testJsWithManifest2(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';
        $file2    = 'test-xyz2.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())
            ->method('__call')
            ->with('url', [$buildDir . '/' . $file, [], [], false])
            ->willReturn($buildDir . '/' . $file2);

        $object->setView($view);

        self::assertSame($buildDir . '/' . $file2, $object->js($name));
    }
}
