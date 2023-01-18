<?php

namespace Hyqo\Router\Test;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Router\MiddlewareInterface;
use Hyqo\Router\Pipeline;
use Hyqo\Router\Router;
use Hyqo\Router\RouterConfiguration;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{

    private function createPipeline(): Pipeline
    {
        $container = new Container();

        $routes = new RouterConfiguration();
        $router = new Router($container, $routes);

        return $container->make(Pipeline::class, ['router' => $router]);
    }

    public function test_middleware()
    {
        $request = new Request();
        $pipeline = $this->createPipeline();

        $pipeline->pipe(function (Request $request, Pipeline $next) {
            $request->attributes->add(['m1' => '']);

            $response = $next($request);

            $response->setHeader('X', 'v');

            return $response;
        });

        $pipeline->pipe(new FooMiddleware());

        $pipeline->pipe([new BarMiddleware(), '__invoke']);

        $response = $pipeline($request);

        $this->assertEquals(['m1' => '', 'm2' => '', 'm3' => ''], $request->attributes->all());
        $this->assertEquals(['HTTP/1.0 200 OK', 'X: v'], $response->headers->all());
    }
}


class FooMiddleware implements MiddlewareInterface
{
    public function __invoke(Request $request, Pipeline $next)
    {
        $request->attributes->add(['m2' => '']);
        return $next($request);
    }
}

class BarMiddleware implements MiddlewareInterface
{
    public function __invoke(Request $request, Pipeline $next)
    {
        $request->attributes->add(['m3' => '']);
        return $next($request);
    }
}
