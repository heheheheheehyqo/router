<?php /** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\GroupConfiguration;
use Hyqo\Router\Route\Route;
use Hyqo\Router\RouterConfiguration;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;

class GroupConfigurationTest extends TestCase
{
    #[ArrayShape([
        'namePropertyReflection' => \ReflectionProperty::class,
        'routerConfigurationPropertyReflection' => \ReflectionProperty::class,
        'configuratorPropertyReflection' => \ReflectionProperty::class,
        'configuredPropertyReflection' => \ReflectionProperty::class,
        'configureMethodReflection' => \ReflectionMethod::class,
    ])]
    protected function reflections(): array
    {
        $reflection = new \ReflectionClass(GroupConfiguration::class);

        $namePropertyReflection = $reflection->getProperty('name');
        $namePropertyReflection->setAccessible(true);

        $routerConfigurationPropertyReflection = $reflection->getProperty('routerConfiguration');
        $routerConfigurationPropertyReflection->setAccessible(true);

        $configuredPropertyReflection = $reflection->getProperty('configured');
        $configuredPropertyReflection->setAccessible(true);

        $configuratorPropertyReflection = $reflection->getProperty('configurator');
        $configuratorPropertyReflection->setAccessible(true);

        $configureMethodReflection = $reflection->getMethod('configure');
        $configureMethodReflection->setAccessible(true);

        return [
            'namePropertyReflection' => $namePropertyReflection,
            'routerConfigurationPropertyReflection' => $routerConfigurationPropertyReflection,
            'configuratorPropertyReflection' => $configuratorPropertyReflection,
            'configuredPropertyReflection' => $configuredPropertyReflection,
            'configureMethodReflection' => $configureMethodReflection,
        ];
    }

    public function test_config(): void
    {
        [
            'namePropertyReflection' => $namePropertyReflection,
            'configuredPropertyReflection' => $configuredPropertyReflection,
            'configuratorPropertyReflection' => $configuratorPropertyReflection,
        ] = $this->reflections();

        $groupConfiguration = new GroupConfiguration();

        $this->assertNull($namePropertyReflection->getValue($groupConfiguration));
        $this->assertFalse($configuredPropertyReflection->getValue($groupConfiguration));
        $this->assertNull($configuratorPropertyReflection->getValue($groupConfiguration));
    }

    public function test_setup(): void
    {
        [
            'routerConfigurationPropertyReflection' => $routerConfigurationPropertyReflection,
            'configuredPropertyReflection' => $configuredPropertyReflection,
            'configuratorPropertyReflection' => $configuratorPropertyReflection,
            'configureMethodReflection' => $configureMethodReflection,
        ] = $this->reflections();

        $groupConfiguration = new GroupConfiguration();

        $setupFunction = fn(RouterConfiguration $routerConfiguration) => $routerConfiguration->add('foo', '/foo');
        $groupConfiguration->setup($setupFunction);
        $this->assertEquals($setupFunction, $configuratorPropertyReflection->getValue($groupConfiguration));

        for ($i = 1; $i <= 2; $i++) {
            $configureMethodReflection->invoke($groupConfiguration);
            $this->assertTrue($configuredPropertyReflection->getValue($groupConfiguration));
        }

        $expectedRouterConfiguration = new RouterConfiguration();
        $setupFunction($expectedRouterConfiguration);

        /** @var RouterConfiguration $routerConfiguration */
        $actualRouterConfiguration = $routerConfigurationPropertyReflection->getValue($groupConfiguration);

        $this->assertEquals($expectedRouterConfiguration, $actualRouterConfiguration);
    }

    public function test_match_group(): void
    {
        $groupConfiguration = (new GroupConfiguration())
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET)
            ->setup(static function (RouterConfiguration $routerConfiguration) {
                $routerConfiguration->add('foo', '/foo', 'fn');
            });

        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'foo.com',
            'REQUEST_URI' => '/foo'
        ]);

        $expectedRoute = new Route(
            name: 'foo',
            pathInfo: '/foo',
            pattern: '/foo',
            tokens: [],
            attributes: [],
            middlewares: [],
            controller: 'fn'
        );

        $this->assertEquals($expectedRoute, $groupConfiguration($request));
    }

    public function test_match_empty_group(): void
    {
        $groupConfiguration = (new GroupConfiguration())
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET);

        $request = new Request(server: [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST' => 'foo.com',
            'REQUEST_URI' => '/foo'
        ]);

        $this->assertNull($groupConfiguration($request, ''));
    }

    /** @dataProvider provide_match_null_data */
    public function test_match_null(Request $request, string $base): void
    {
        $groupConfiguration = (new GroupConfiguration())
            ->setPrefix('/sub')
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET);

        $this->assertNull($groupConfiguration($request, $base));
    }

    protected function provide_match_null_data(): \Generator
    {
        yield [new Request(server: ['REQUEST_METHOD' => 'POST']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/sub'];
    }

    public function test_map_generator(): void
    {
        $groupConfiguration = (new GroupConfiguration())
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET)
            ->setup(static function (RouterConfiguration $routerConfiguration) {
                $routerConfiguration->add('foo', '/foo', 'fn');
            });

        $this->assertEquals([
            'foo' => new Route(
                name: 'foo',
                pathInfo: '',
                pattern: '/foo',
                tokens: [],
                attributes: [],
                middlewares: [],
                controller: 'fn'
            )
        ], iterator_to_array($groupConfiguration));
    }
}
