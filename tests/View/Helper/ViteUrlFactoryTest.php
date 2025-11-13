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

use AssertionError;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class ViteUrlFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutDirs(): void
    {
        $config = [];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
        $container->expects(self::never())
            ->method('has');

        $result = (new ViteUrlFactory())($container, '');

        self::assertInstanceOf(ViteUrl::class, $result);
        self::assertNull($result->getPublicDir());
        self::assertNull($result->getBuildDir());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithDirs(): void
    {
        $publicDir = 'test-public-dir';
        $buildDir  = 'test-build-dir';
        $config    = ['vite-url' => ['public-dir' => $publicDir, 'build-dir' => $buildDir]];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
        $container->expects(self::never())
            ->method('has');

        $result = (new ViteUrlFactory())($container, '');

        self::assertInstanceOf(ViteUrl::class, $result);
        self::assertSame($publicDir, $result->getPublicDir());
        self::assertSame($buildDir, $result->getBuildDir());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithoutConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn(null);
        $container->expects(self::never())
            ->method('has');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert(is_array($config))');

        (new ViteUrlFactory())($container, '');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithWrongRouteType(): void
    {
        $config = ['vite-url' => ['route' => 42]];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
        $container->expects(self::never())
            ->method('has');

        $result = (new ViteUrlFactory())($container, '');

        self::assertInstanceOf(ViteUrl::class, $result);
        self::assertNull($result->getPublicDir());
        self::assertNull($result->getBuildDir());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     * @throws NoPreviousThrowableException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testInvokeWithWongRouteType2(): void
    {
        $config = ['vite-url' => 42];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
        $container->expects(self::never())
            ->method('has');

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage('assert(is_array($config))');

        (new ViteUrlFactory())($container, '');
    }
}
