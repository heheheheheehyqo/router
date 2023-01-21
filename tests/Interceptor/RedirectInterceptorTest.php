<?php /** @noinspection PsalmAdvanceCallableParamsInspection */

/** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test\Interceptor;

use Hyqo\Container\Container;
use Hyqo\Http\HttpCode;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\RedirectException;
use Hyqo\Router\Interceptor\RedirectInterceptor;
use Hyqo\Router\Router;
use Hyqo\Router\RouterConfiguration;
use Hyqo\Router\Service\UrlService;
use PHPUnit\Framework\TestCase;

class RedirectInterceptorTest extends TestCase
{
    public function test_http_code(): void
    {
        $reflection = new \ReflectionClass(RedirectInterceptor::class);
        $getHttCodeMethodReflection = $reflection->getMethod('getHttpCode');
        $getHttCodeMethodReflection->setAccessible(true);

        $redirect = new RedirectInterceptor();

        $this->assertEquals(HttpCode::FOUND, $getHttCodeMethodReflection->invoke($redirect));

        $redirect->setHttpCode(HttpCode::MOVED_PERMANENTLY);
        $this->assertEquals(HttpCode::MOVED_PERMANENTLY, $getHttCodeMethodReflection->invoke($redirect));
    }

    public function test_location(): void
    {
        $reflection = new \ReflectionClass(RedirectInterceptor::class);
        $locationPropertyReflection = $reflection->getProperty('location');
        $locationPropertyReflection->setAccessible(true);

        $redirect = new RedirectInterceptor();

        $this->assertNull($locationPropertyReflection->getValue($redirect));

        $redirect->toLocation('/foo');
        $this->assertEquals('/foo', $locationPropertyReflection->getValue($redirect));
    }

    protected function getHandlerParameters(): array
    {
        $container = new Container();
        $routes = new RouterConfiguration();
        $routes->add('foo', '/foo', fn() => '');

        $router = new Router($container, $routes);
        $request = new Request();
        $urlService = $container->get(UrlService::class);

        return [
            'router' => $router,
            'request' => $request,
            'urlService' => $urlService,
        ];
    }

    public function test_call_empty_handler(): void
    {
        $redirect = new RedirectInterceptor();

        $this->expectException(RedirectException::class);
        $redirect->getHandler()(...$this->getHandlerParameters());
    }

    public function test_redirect_to_route(): void
    {
        $redirect = new RedirectInterceptor();
        $redirect->toRoute('foo');

        $response = $redirect->getHandler()(...$this->getHandlerParameters());
        $this->assertEquals([
            'HTTP/1.0 302 Found',
            'Location: /foo'
        ], $response->headers->all());
    }

    public function test_redirect_to_location(): void
    {
        $redirect = new RedirectInterceptor();
        $redirect->toLocation('/bar');

        $response = $redirect->getHandler()(...$this->getHandlerParameters());

        $this->assertEquals([
            'HTTP/1.0 302 Found',
            'Location: /bar'
        ], $response->headers->all());
    }

    public function test_get_handler(): void
    {
        $redirect = new RedirectInterceptor();

        $handler = $redirect->getHandler();

        $handlerReflection = new \ReflectionFunction($handler);

        $handlerParameters = $handlerReflection->getParameters();
        $handlerReturnType = $handlerReflection->getReturnType();

        $this->assertEquals('router', $handlerParameters[0]->name);
        $this->assertEquals(Router::class, $handlerParameters[0]->getType()->getName());

        $this->assertEquals('request', $handlerParameters[1]->name);
        $this->assertEquals(Request::class, $handlerParameters[1]->getType()->getName());

        $this->assertEquals('urlService', $handlerParameters[2]->name);
        $this->assertEquals(UrlService::class, $handlerParameters[2]->getType()->getName());

        $this->assertEquals(Response::class, $handlerReturnType->getName());
    }

}
