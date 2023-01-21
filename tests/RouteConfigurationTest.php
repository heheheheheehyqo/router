<?php /** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\Route\Route;
use Hyqo\Router\RouteConfiguration;
use PHPUnit\Framework\TestCase;

class RouteConfigurationTest extends TestCase
{
    public function test_set_controller(): void
    {
        $reflection = new \ReflectionClass(RouteConfiguration::class);
        $controllerPropertyReflection = $reflection->getProperty('controller');
        $controllerPropertyReflection->setAccessible(true);

        $routeConfiguration = new RouteConfiguration('foo', '/foo');
        $this->assertNull($controllerPropertyReflection->getValue($routeConfiguration));

        $routeConfiguration->setController('bar');
        $this->assertEquals('bar', $controllerPropertyReflection->getValue($routeConfiguration));
    }

    public function test_match_route(): void
    {
        $routeConfiguration = (new RouteConfiguration('foo', '/foo'))
            ->setController('bar')
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET);

        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'foo.com',
            'REQUEST_URI' => '/foo'
        ]);

        $this->assertInstanceOf(Route::class, $routeConfiguration($request, ''));
    }

    /** @dataProvider provide_match_null_data */
    public function test_match_null(Request $request, string $base): void
    {
        $routeConfiguration = (new RouteConfiguration('foo', '/foo'))
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET);

        $this->assertNull($routeConfiguration($request, $base));
    }

    protected function provide_match_null_data(): \Generator
    {
        yield [new Request(server: ['REQUEST_METHOD' => 'POST']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/foo'];
    }
}
