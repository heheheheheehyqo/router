<?php

namespace Hyqo\Router\Test\Middleware;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Router\Middleware\ProfilerMiddleware;
use Hyqo\Router\Pipeline;
use Hyqo\Router\Router;
use Hyqo\Router\RouterConfiguration;
use PHPUnit\Framework\TestCase;

class ProfilerMiddlewareTest extends TestCase
{
    public function test_middleware(): void
    {
        $container = new Container();

        $routes = new RouterConfiguration();
        $router = new Router($container, $routes);

        $request = new Request();
        $pipeline = $container->make(Pipeline::class, ['router' => $router]);

        $pipeline->pipe([new ProfilerMiddleware(), '__invoke']);

        $response = $pipeline($request);

        $this->assertStringStartsWith('X-Profiler-ET', $response->headers->all()[1]);
    }

}
