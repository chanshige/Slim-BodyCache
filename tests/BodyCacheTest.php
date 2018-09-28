<?php
namespace Chanshige\Slim\BodyCache;

use PHPUnit\Framework\TestCase;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Cache\Simple\FilesystemCache;

/**
 * Class BodyCacheTest
 *
 * @package Chanshige\Slim\BodyCache
 */
class BodyCacheTest extends TestCase
{
    use DevApp;

    /** @var FilesystemCache */
    private static $fileCache;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        self::$fileCache = new FilesystemCache('test_cache', 180, TEST_DIR);
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::$fileCache->clear();
        exec('rm -rf ' . TEST_DIR . 'test_cache');
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $message
     * @param bool   $expectedCache
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @dataProvider requestProvider
     */
    public function testCache($method, $path, $message, $expectedCache)
    {
        $this->initialize($method, $path);
        $middleware = new Cache(self::$fileCache);

        $hasCache = null;
        // callable
        $next = function (Request $request, Response $response) use (&$hasCache) {
            $hasCache = $request->getAttribute('has_cache');
            return $response;
        };

        if (strlen($message) > 0 && !$expectedCache) {
            $body = $this->response->getBody();
            $body->write($message);
            $this->response->withBody($body);
        }

        $middleware($this->request, $this->response, $next);

        $this->assertSame($expectedCache, $hasCache);
        $this->assertSame($message, (string)$this->response->getBody());
    }

    /**
     * TestPattern.
     *
     * @return array
     */
    public function requestProvider()
    {
        return [
            [
                'GET',
                '/foo',
                'Hello World!',
                false,
            ],
            [
                'GET',
                '/var',
                'Hello!!',
                false,
            ],
            [
                'GET',
                '/foo',
                'Hello World!',
                true,
            ],
            [
                'GET',
                '/var',
                'Hello!!',
                true,
            ]
        ];
    }
}
