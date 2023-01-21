<?php /** @noinspection ForgottenDebugOutputInspection */

namespace Hyqo\Router\Test;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Exception\UndefinedRouteException;
use Hyqo\Router\MiddlewareInterface;
use Hyqo\Router\Pipeline;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Router;
use Hyqo\Router\RouterConfiguration;
use PHPUnit\Framework\TestCase;

use function Hyqo\Router\not_found;

class RouterTest extends TestCase
{
    public function test_get_route(): void
    {
        $routes = new RouterConfiguration();
        $routes->add('foo', '/', 'action');

        $router = new Router(new Container(), $routes);

        $route = $router->getRoute('foo');
        $this->assertEquals(
            new Route(
                name: 'foo',
                pathInfo: '',
                pattern: '/',
                tokens: [],
                attributes: [],
                middlewares: [],
                controller: 'action',
                fallback: null,
            ),
            $route
        );

        $this->expectException(UndefinedRouteException::class);
        $router->getRoute('bar');
    }

    public function test_route_not_found(): void
    {
        $routes = new RouterConfiguration();

        $request = new Request(server: ['REQUEST_URI' => '/']);
        $router = new Router(new Container(), $routes);

        $this->expectException(NotFoundException::class);
        $router->handle($request);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_throw_not_found(): void
    {
        $routes = new RouterConfiguration();
        $routes->add('home', '/', function () {
            not_found();
        });

        $routes->addGroup()->setup(function (RouterConfiguration $routerConfiguration) {
            $routerConfiguration->setFallback(fn() => 'fallback');
            $routerConfiguration->add('foo', '/foo', function () {
                not_found();
            });
        });

        $router = new Router(new Container(), $routes);

        $response = $router->handle(new Request(server: ['REQUEST_URI' => '/foo']));
        ob_start();
        $response->send();
        $output = ob_end_clean();
        $this->assertEquals('fallback', $output);

        $this->expectException(NotFoundException::class);
        $router->handle(new Request(server: ['REQUEST_URI' => '/']));
    }

    /**
     * @runInSeparateProcess
     * @dataProvider provide_remove_slashes_data
     */
    public function test_remove_slashes(string $expectedLocation, string $requestUri): void
    {
        $routes = new RouterConfiguration();

        $request = new Request(server: ['REQUEST_URI' => $requestUri]);
        $router = new Router(new Container(), $routes);

        $response = $router->handle($request);
        $response->send();

        $this->assertEquals(["Location: $expectedLocation"], xdebug_get_headers());
    }

    protected function provide_remove_slashes_data(): \Generator
    {
        yield ['/foo', '/foo/'];
        yield ['/foo', '/foo//'];
        yield ['/foo?foo=bar', '/foo//?foo=bar'];
    }

    /** @runInSeparateProcess */
    public function test_handle(): void
    {
        $routes = new RouterConfiguration();
        $routes->add('foo', '/foo', fn() => 'text');
        $routes->addMiddleware(Middleware::class);

        $request = new Request(server: ['REQUEST_URI' => '/foo']);

        $router = new Router(new Container(), $routes);
        $response = $router->handle($request);

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('override', $output);
    }
}

class Middleware implements MiddlewareInterface
{
    public function __invoke(Request $request, Pipeline $next)
    {
        return $next($request)->setContent('override');
    }
}
