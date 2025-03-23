<?php

/**
 * This file is part of the mimmi20/laminasviewrenderer-vite-url package.
 *
 * Copyright (c) 2023-2025, Thomas Mueller <mimmi20@live.de>
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
use PHPUnit\Event\NoPreviousThrowableException;
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

        $object->js('');
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

        $object->js('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
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

        $object->js('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithHotRelaoding(): void
    {
        $root   = vfsStream::setup('root');
        $hotDir = 'test-hot-dir';
        $name   = 'test.js';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent($hotDir);

        $root->addChild($file1);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertSame($hotDir . '/' . $name, $object->js($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithHotRelaoding2(): void
    {
        $root   = vfsStream::setup('root');
        $hotDir = 'test-hot-dir';
        $name   = 'test.js';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent($hotDir . ' ');

        $root->addChild($file1);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertSame($hotDir . '/' . $name, $object->js($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithHotRelaoding3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent('');

        $root->addChild($file1);

        $manifestPathV4 = $root->url() . '/' . $buildDir . '/manifest.json';
        $manifestPathV5 = $root->url() . '/' . $buildDir . '/.vite/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s or at %s', $manifestPathV4, $manifestPathV5),
        );

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithoutManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $dir = vfsStream::newDirectory($buildDir);

        $root->addChild($dir);

        $publicDir      = 'test-public-dir';
        $manifestPathV4 = $publicDir . '/' . $buildDir . '/manifest.json';
        $manifestPathV5 = $publicDir . '/' . $buildDir . '/.vite/manifest.json';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s or at %s', $manifestPathV4, $manifestPathV5),
        );

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
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
        $this->expectExceptionMessage(sprintf('Unknown Vite JS entrypoint %s', $name));

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
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
            ->with('serverUrl', ['/' . $buildDir . '/' . $file])
            ->willReturn('/' . $buildDir . '/' . $file2);

        $object->setView($view);

        self::assertSame('/' . $buildDir . '/' . $file2, $object->js($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithManifest3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = null;

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Build Dir is required');

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithManifest4(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('');

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);
        $manifestPath = $root->url() . '/' . $buildDir . '/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not read Vite manifest at: %s', $manifestPath),
        );

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithManifest5(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('{test:');

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);
        $manifestPath = $root->url() . '/' . $buildDir . '/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not decode Vite manifest at: %s', $manifestPath),
        );

        $object->js($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testJsWithManifest6(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';
        $file2    = 'test-xyz2.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())
            ->method('__call')
            ->with('serverUrl', ['/' . $buildDir . '/' . $file])
            ->willReturn('/' . $buildDir . '/' . $file2);

        $object->setView($view);

        self::assertSame('/' . $buildDir . '/' . $file2, $object->js($name));
    }

    /** @throws RuntimeException */
    public function testCssWithoutPublicDir(): void
    {
        $publicDir = null;
        $buildDir  = null;

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Public Dir is required');

        $object->css('');
    }

    /** @throws RuntimeException */
    public function testCssWithoutRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->css('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithWrongRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(RendererInterface::class);

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->css('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithHotRelaoding(): void
    {
        $root   = vfsStream::setup('root');
        $hotDir = 'test-hot-dir';
        $name   = 'test.css';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent($hotDir);

        $root->addChild($file1);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertSame($hotDir . '/' . $name, $object->css($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithHotRelaoding2(): void
    {
        $root   = vfsStream::setup('root');
        $hotDir = 'test-hot-dir';
        $name   = 'test.css';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent($hotDir . ' ');

        $root->addChild($file1);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertSame($hotDir . '/' . $name, $object->css($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithHotRelaoding3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('hot', 0777);
        $file1->setContent('');

        $root->addChild($file1);

        $manifestPathV4 = $root->url() . '/' . $buildDir . '/manifest.json';
        $manifestPathV5 = $root->url() . '/' . $buildDir . '/.vite/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s or at %s', $manifestPathV4, $manifestPathV5),
        );

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithoutManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';

        $dir = vfsStream::newDirectory($buildDir);

        $root->addChild($dir);

        $publicDir      = 'test-public-dir';
        $manifestPathV4 = $publicDir . '/' . $buildDir . '/manifest.json';
        $manifestPathV5 = $publicDir . '/' . $buildDir . '/.vite/manifest.json';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Vite manifest not found at %s or at %s', $manifestPathV4, $manifestPathV5),
        );

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
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
        $this->expectExceptionMessage(sprintf('Unknown Vite CSS entrypoint %s', $name));

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest2(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.css';
        $file2    = 'test-xyz2.css';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())
            ->method('__call')
            ->with('serverUrl', ['/' . $buildDir . '/' . $file])
            ->willReturn('/' . $buildDir . '/' . $file2);

        $object->setView($view);

        self::assertSame('/' . $buildDir . '/' . $file2, $object->css($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest3(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = null;

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Build Dir is required');

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest4(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('');

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);
        $manifestPath = $root->url() . '/' . $buildDir . '/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not read Vite manifest at: %s', $manifestPath),
        );

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest5(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent('{test:');

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);
        $manifestPath = $root->url() . '/' . $buildDir . '/manifest.json';

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            sprintf('Could not decode Vite manifest at: %s', $manifestPath),
        );

        $object->css($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCssWithManifest6(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.css';
        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.css';
        $file2    = 'test-xyz2.css';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir2 = vfsStream::newDirectory('.vite');
        $dir2->addChild($file1);

        $dir1 = vfsStream::newDirectory($buildDir);
        $dir1->addChild($dir2);

        $root->addChild($dir1);

        $object = new ViteUrl($root->url(), $buildDir);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())
            ->method('__call')
            ->with('serverUrl', ['/' . $buildDir . '/' . $file])
            ->willReturn('/' . $buildDir . '/' . $file2);

        $object->setView($view);

        self::assertSame('/' . $buildDir . '/' . $file2, $object->css($name));
    }
}
