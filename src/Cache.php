<?php
declare(strict_types=1);

namespace Chanshige\Slim\BodyCache;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 *
 * @package Chanshige\Slim\BodyCache
 */
final class Cache
{
    /**
     * @var CacheInterface $cache
     */
    private $cache;

    /**
     * The 'has_cache' attribute name.
     *
     * @var string
     */
    private $hasCacheName = 'has_body_cache';

    /**
     * Cache constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Invoke.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $cacheId = $this->makeCacheId($request);
        $hasCache = $this->cache->has($cacheId);
        $request = $request->withAttribute($this->hasCacheName, $hasCache);

        if (!$hasCache) {
            /** @var ResponseInterface $response */
            $response = $next($request, $response);
            $this->cache->set($cacheId, (string)$response->getBody());

            return $response;
        }

        $body = $response->getBody();
        $body->write($this->cache->get($cacheId));
        /** @var ResponseInterface $response */
        $response = $response->withBody($body);

        return $next($request, $response);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function makeCacheId(ServerRequestInterface $request): string
    {
        return str_replace('/', '_', $request->getUri()->getPath()) .
            sha1($request->getUri()->getQuery());
    }
}
