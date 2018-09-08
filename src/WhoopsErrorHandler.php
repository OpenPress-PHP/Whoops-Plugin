<?php
namespace OpenPress\Plugin\Whoops;

use Slim\Http\Request;
use Slim\Http\Response;
use Whoops\Run as WhoopsRun;

class WhoopsErrorHandler
{
    private $whoops;
    public function __construct(WhoopsRun $whoops)
    {
        $this->whoops = $whoops;
    }

    public function __invoke(Request $request, Response $response, $throwable)
    {
        $handler = WhoopsRun::EXCEPTION_HANDLER;

        ob_start();
        $this->whoops->$handler($throwable);
        $content = ob_get_clean();

        $code    = $throwable instanceof HttpException ? $throwable->getStatusCode() : 500;

        return $response
                ->withStatus($code)
                ->withHeader('Content-type', 'text/html')
                ->write($content);
    }
}
