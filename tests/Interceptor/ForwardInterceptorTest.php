<?php /** @noinspection PsalmAdvanceCallableParamsInspection */

/** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test\Interceptor;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\ForwardException;
use Hyqo\Router\Interceptor\ForwardInterceptor;
use Hyqo\Router\Router;
use Hyqo\Router\RouterConfiguration;
use Hyqo\Router\Service\CallableService;
use PHPUnit\Framework\TestCase;

class ForwardInterceptorTest extends TestCase
{
    public function test_controller(): void
    {
        $reflection = new \ReflectionClass(ForwardInterceptor::class);
        $controllerPropertyReflection = $reflection->getProperty('controller');
        $controllerPropertyReflection->setAccessible(true);

        $redirect = new ForwardInterceptor();

        $this->assertNull($controllerPropertyReflection->getValue($redirect));

        $redirect->toController('foo');
        $this->assertEquals('foo', $controllerPropertyReflection->getValue($redirect));
    }

    protected function getHandlerParameters(): array
    {
        $container = new Container();
        $routes = new RouterConfiguration();
        $routes->add('foo', '/foo', fn() => new Response(content: 'bar'));

        $router = new Router($container, $routes);
        $request = new Request();
        $callableService = $container->get(CallableService::class);

        return [
            'router' => $router,
            'request' => $request,
            'callableService' => $callableService,
        ];
    }

    public function test_call_empty_handler(): void
    {
        $forward = new ForwardInterceptor();

        $this->expectException(ForwardException::class);
        $forward->getHandler()(...$this->getHandlerParameters());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_forward_to_route(): void
    {
        $forward = new ForwardInterceptor();
        $forward->toRoute('foo');

        $response = $forward->getHandler()(...$this->getHandlerParameters());
        ob_start();
        $response->send();
        $output = ob_end_clean();

        $this->assertEquals('bar', $output);
    }

    /**
     * @runInSeparateProcess
     */
    public function test_forward_to_controller(): void
    {
        $forward = new ForwardInterceptor();
        $forward->toController(fn(Request $request) => $request->get('bar'), ['bar' => 'baz']);

        $response = $forward->getHandler()(...$this->getHandlerParameters());
        ob_start();
        $response->send();
        $output = ob_end_clean();

        $this->assertEquals('baz', $output);
    }

    public function test_get_handler(): void
    {
        $forward = new ForwardInterceptor();

        $handler = $forward->getHandler();

        $handlerReflection = new \ReflectionFunction($handler);

        $handlerParameters = $handlerReflection->getParameters();
        $handlerReturnType = $handlerReflection->getReturnType();

        $this->assertEquals('router', $handlerParameters[0]->name);
        $this->assertEquals(Router::class, $handlerParameters[0]->getType()->getName());

        $this->assertEquals('request', $handlerParameters[1]->name);
        $this->assertEquals(Request::class, $handlerParameters[1]->getType()->getName());

        $this->assertEquals('callableService', $handlerParameters[2]->name);
        $this->assertEquals(CallableService::class, $handlerParameters[2]->getType()->getName());

        $this->assertEquals(Response::class, $handlerReturnType->getName());
    }

}
