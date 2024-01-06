<?php
/**
 * This file is part of the mimmi20/laminasviewrenderer-vite-url package.
 *
 * Copyright (c) 2023-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\LaminasView\ViteUrl\View\Helper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function is_string;

/**
 * Generates the BootstrapFlashMessenger view helper object
 */
final class ViteUrlFactory implements FactoryInterface
{
    /**
     * Create Service Factory
     *
     * @param string            $requestedName
     * @param array<mixed>|null $options
     * @phpstan-param array<mixed>|null $options
     *
     * @throws ContainerExceptionInterface
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __invoke(ContainerInterface $container, $requestedName, array | null $options = null): ViteUrl
    {
        $config = $container->get('config');
        assert(is_array($config));

        $config = $config['vite-url'] ?? [];
        assert(is_array($config));

        $publicDir = $config['public-dir'] ?? null;

        if (!is_string($publicDir)) {
            $publicDir = null;
        }

        $buildDir = $config['build-dir'] ?? null;

        if (!is_string($buildDir)) {
            $buildDir = null;
        }

        return new ViteUrl($publicDir, $buildDir);
    }
}
