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
    public function testFileWithoutPublicDir(): void
    {
        $publicDir = null;
        $buildDir  = null;

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A Public Dir is required');

        $object->file('');
    }

    /** @throws RuntimeException */
    public function testFileWithoutRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->file('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithWrongRenderer(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';

        $object = new ViteUrl($publicDir, $buildDir);

        $view = $this->createMock(RendererInterface::class);

        $object->setView($view);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('A PHP View Renderer is required');

        $object->file('');
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithHotRelaoding(): void
    {
        $root   = vfsStream::setup('root');
        $hotUrl = 'https://test.hot.dir';
        $name   = 'test.js';

        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir, $hotUrl);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertTrue($object->isDev());
        self::assertSame($hotUrl . '/' . $buildDir . '/' . $file, $object->file($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithHotRelaoding3(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithHotRelaoding4(): void
    {
        $root     = vfsStream::setup('root');
        $name     = 'test.js';
        $buildDir = 'test-build-dir';

        $file1 = vfsStream::newFile('hot', 0333);
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithHotRelaoding5(): void
    {
        $root   = vfsStream::setup('root');
        $hotUrl = 'https://test.hot.dir';
        $name   = 'test.js';
        $name2  = '@vite/client';

        $buildDir = 'test-build-dir';
        $file     = 'test-xyz.js';

        $file1 = vfsStream::newFile('manifest.json', 0777);
        $file1->setContent((string) json_encode([$name => ['file' => $file]]));

        $dir = vfsStream::newDirectory($buildDir);
        $dir->addChild($file1);

        $root->addChild($dir);

        $buildDir = 'test-build-dir';

        $object = new ViteUrl($root->url(), $buildDir, $hotUrl);

        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::never())
            ->method('__call');

        $object->setView($view);

        self::assertTrue($object->isDev());
        self::assertSame($hotUrl . '/' . $name2, $object->file($name2));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithoutManifest(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest2(): void
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

        self::assertSame('/' . $buildDir . '/' . $file2, $object->file($name));
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest3(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest4(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest5(): void
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

        $object->file($name);
    }

    /**
     * @throws Exception
     * @throws RuntimeException
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFileWithManifest6(): void
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

        self::assertSame('/' . $buildDir . '/' . $file2, $object->file($name));
    }
}
