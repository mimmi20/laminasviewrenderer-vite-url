# laminasviewrenderer-vite-url

[![Latest Stable Version](https://poser.pugx.org/mimmi20/laminasviewrenderer-vite-url/v/stable?format=flat-square)](https://packagist.org/packages/mimmi20/laminasviewrenderer-vite-url)
[![Latest Unstable Version](https://poser.pugx.org/mimmi20/laminasviewrenderer-vite-url/v/unstable?format=flat-square)](https://packagist.org/packages/mimmi20/laminasviewrenderer-vite-url)
[![License](https://poser.pugx.org/mimmi20/laminasviewrenderer-vite-url/license?format=flat-square)](https://packagist.org/packages/mimmi20/laminasviewrenderer-vite-url)

## Code Status

[![codecov](https://codecov.io/gh/mimmi20/laminasviewrenderer-vite-url/branch/master/graph/badge.svg)](https://codecov.io/gh/mimmi20/laminasviewrenderer-vite-url)
[![Average time to resolve an issue](https://isitmaintained.com/badge/resolution/mimmi20/laminasviewrenderer-vite-url.svg)](https://isitmaintained.com/project/mimmi20/laminasviewrenderer-vite-url "Average time to resolve an issue")
[![Percentage of issues still open](https://isitmaintained.com/badge/open/mimmi20/laminasviewrenderer-vite-url.svg)](https://isitmaintained.com/project/mimmi20/laminasviewrenderer-vite-url "Percentage of issues still open")
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fmimmi20%2Flaminasviewrenderer-vite-url%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/mimmi20/laminasviewrenderer-vite-url/master)

## Introduction

This component provides a view helper to render urls for resources build with Vite

## Requirements

This library requires PHP 8.3+.

## Installation

Run

```shell
composer require mimmi20/laminasviewrenderer-vite-url
```

## Prepare Vite

In your Vite config, you need to activate the manifest. 

```js
  publicDir: 'public',
  base: '/dist/',
  build: {
    // ...
    outDir: 'public/dist', // relative to the `root` folder
    manifest: true,
    // ...
    
    rollupOptions: {
      input: [
        path.resolve(__dirname, 'public/css/styles.css'),
        path.resolve(__dirname, 'public/scss/styles.scss'),
      ]
    }
  }
```

The required manifest file and the resorce files are created when running Vite's build command.

```shell
npx vite build
```

## Config

This viewhelper needs a config to know where the public and the build directories are. The directories have to match the directories configured for Vite.

```php
<?php
return [
    // ...
    'vite-url' => [
        'public-dir' => 'public', // <-- relative to the project root
        'build-dir' => 'dist',    // <-- relative to the public directory
    ],
    // ...
];
```

## Usage

Now you may add a file build with Vite. It is nessesary to use the exact path, you use in the Vite config. Otherwise the file can not be found in the manifest.

```php
    $this->headLink()->appendStylesheet($this->viteUrl()->file('public/css/styles.css'), 'screen', ['rel' => 'stylesheet']);
    $this->headLink()->appendStylesheet($this->viteUrl()->file('public/scss/styles.scss'), 'screen', ['rel' => 'stylesheet']);
```

## License

This package is licensed using the MIT License.

Please have a look at [`LICENSE.md`](LICENSE.md).
