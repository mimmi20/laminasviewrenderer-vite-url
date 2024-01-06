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

namespace Mimmi20\LaminasView\ViteUrl;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

final class Module implements ConfigProviderInterface
{
    /**
     * Returns configuration to merge with application configuration
     *
     * @return array<array<array<string>>>
     * @phpstan-return array{view_helpers: array{aliases: non-empty-array<string, class-string>, factories: non-empty-array<class-string, class-string>}}
     *
     * @throws void
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'view_helpers' => $provider->getViewHelperConfig(),
        ];
    }
}
