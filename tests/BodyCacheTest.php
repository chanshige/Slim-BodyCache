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
        exec('rm -rf ' . TEST_DIR . 'test_cache');
    }

    /**
     * @param string $method
     * @param string $path
     * @param string $message
     * @param bool   $hasCache
     * @dataProvider requestProvider
     */
    public function testCache(string $method, string $path, string $message, bool $hasCache)
    {
        $this->initialize($method, $path);
        $next = function (Request $request, Response $response) use ($message, $hasCache) {
            $hasBodyCache = $request->getAttribute('has_body_cache');
            if ($hasBodyCache) {
                $this->assertTrue($hasBodyCache);
                $this->assertSame($hasCache, $hasBodyCache);
                $this->assertEquals($message, (string)$response->getBody());
                return $response;
            }

            $this->assertFalse($hasBodyCache);
            $this->assertSame($hasCache, $hasBodyCache);

            $body = $response->getBody();
            $body->write($message);

            return $response->withBody($body);
        };

        (new Cache(self::$fileCache))($this->request, $this->response, $next);
    }

    /**
     * @void
     */
    public function testClearCache()
    {
        $this->assertTrue((new Cache(self::$fileCache))->clear());
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
                'Foo Body!',
                false,
            ],
            [
                'GET',
                '/var',
                'Var Body!',
                false,
            ],
            [
                'GET',
                '/foo',
                'Foo Body!',
                true,
            ],
            [
                'GET',
                '/var',
                'Var Body!',
                true,
            ]
        ];
    }
}
