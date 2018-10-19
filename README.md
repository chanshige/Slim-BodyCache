# Slim Framework Response Body Cache

[![Packagist](https://img.shields.io/badge/packagist-v0.0.1-blue.svg)](https://packagist.org/packages/chanshige/slim-bodycache)
[![Build Status](https://travis-ci.org/chanshige/Slim-BodyCache.svg?branch=master)](https://travis-ci.org/chanshige/Slim-BodyCache)

  
A slim middleware for Filesystem Cache based on symfony/cache.  
  
## Install

Via Composer  

``` bash
$ composer require chanshige/slim-bodycache
  
# symfony/cacheを使う場合はこちらも   
$ composer require symfony/cache
```

Requires Slim 3.0.0 or newer.

## Usage

```php
<?php

$app = new \Slim\App();

// CacheConfig
$namespace = 'cache';
$defaultLifetime = 3600;
$directory = '/path/to/dir';

// Symfony Filesystem Cache
// PSR-6/16 Interfaceを持つコンポーネントを使用
$sfCache = new \Symfony\Component\Cache\Simple\FilesystemCache(
    $namespace,
    $defaultLifetime,
    $directory
);

// Register middleware
$app->add(new \Chanshige\Slim\BodyCache\Cache($sfCache));

// Fetch DI Container
$container = $app->getContainer();

$container['body_cache'] = function () use ($sfCache) {
    return new \Chanshige\Slim\BodyCache\Cache($sfCache);
};

// Json Response
$app->get('/foo', function (\Slim\Http\Request $request, \Slim\Http\Response $response) {
    
    if($request->getAttribute('has_body_cache')) {
        return $response
            ->withHeader("Content-type", "application/json;charset=utf-8");
    }

    return $response
        ->withStatus(200)
        ->withJson(['Hello World!!']);
})->add($container->get('body_cache'));

$app->run();
```