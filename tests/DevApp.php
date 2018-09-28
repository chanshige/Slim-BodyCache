<?php
namespace Chanshige\Slim\BodyCache;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Trait DevApp
 *
 * @package Chanshige\Slim\BodyCache
 */
trait DevApp
{
    /** @var Request $request */
    protected $request;

    /** @var Response $response */
    protected $response;

    /**
     * Initialize.
     *
     * @param string $method
     * @param string $path
     * @param array  $query
     * @param string $data
     */
    public function initialize($method = 'GET', $path = '/', $query = [], $data = '')
    {
        $this->request = $this->requestFactory($method, $path, $query);
        $this->response = $this->responseFactory($data);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $query
     * @return Request
     */
    public function requestFactory($method = 'GET', $path = '/', $query = [])
    {
        $environment = Environment::mock(
            [
                'SCRIPT_NAME' => '/index.php',
                'METHOD' => $method,
                'REQUEST_URI' => $path,
                'QUERY_STRING' => http_build_query($query)
            ]
        );

        return Request::createFromEnvironment($environment);
    }

    /**
     * @param string $data
     * @return Response
     */
    public function responseFactory($data = '')
    {
        $response = new Response();
        if (strlen($data) > 0) {
            $response->write($data);
        }

        return $response;
    }
}
