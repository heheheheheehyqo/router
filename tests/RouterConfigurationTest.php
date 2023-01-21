<?php /** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\ConfigurationCollection;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\GroupConfiguration;
use Hyqo\Router\RouteConfiguration;
use Hyqo\Router\RouterConfiguration;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;

class RouterConfigurationTest extends TestCase
{
    #[ArrayShape([
        'configurationsPropertyReflection' => \ReflectionProperty::class,
        'fallbackPropertyReflection' => \ReflectionProperty::class,
    ])]
    protected function reflection(): array
    {
        $reflection = new \ReflectionClass(RouterConfiguration::class);

        $configurationsPropertyReflection = $reflection->getProperty('configurations');
        $configurationsPropertyReflection->setAccessible(true);

        $fallbackPropertyReflection = $reflection->getProperty('fallback');
        $fallbackPropertyReflection->setAccessible(true);

        return [
            'configurationsPropertyReflection' => $configurationsPropertyReflection,
            'fallbackPropertyReflection' => $fallbackPropertyReflection,
        ];
    }

    public function test_add(): void
    {
        [
            'configurationsPropertyReflection' => $configurationsPropertyReflection
        ] = $this->reflection();

        $routerConfiguration = new RouterConfiguration();
        $expectedCollection = new ConfigurationCollection();

        $this->assertEquals(
            $expectedCollection,
            $configurationsPropertyReflection->getValue($routerConfiguration)
        );

        $routerConfiguration->add('foo', '/foo');
        $expectedCollection->add(new RouteConfiguration('foo', '/foo', null));

        $this->assertEquals(
            $expectedCollection,
            $configurationsPropertyReflection->getValue($routerConfiguration)
        );

        $routerConfiguration->addGroup('bar');
        $expectedCollection->add(new GroupConfiguration('bar'));

        $this->assertEquals(
            $expectedCollection,
            $configurationsPropertyReflection->getValue($routerConfiguration)
        );
    }

    public function test_fallback(): void
    {
        [
            'fallbackPropertyReflection' => $fallbackPropertyReflection
        ] = $this->reflection();

        $routerConfiguration = new RouterConfiguration();

        $this->assertEquals(null, $fallbackPropertyReflection->getValue($routerConfiguration));

        $routerConfiguration->setFallback('foo');

        $this->assertEquals('foo', $fallbackPropertyReflection->getValue($routerConfiguration));
    }

    /** @dataProvider provide_match_null_data */
    public function test_match_null(Request $request, string $base): void
    {
        $routerConfiguration = (new RouterConfiguration())
            ->setPrefix('/sub')
            ->acceptHost('foo.com')
            ->acceptMethod(Method::GET);

        $this->assertNull($routerConfiguration($request, $base));
    }

    protected function provide_match_null_data(): \Generator
    {
        yield [new Request(server: ['REQUEST_METHOD' => 'POST']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET']), '/foo'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/'];
        yield [new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']), '/sub'];
    }

    public function test_match_fallback(): void
    {
        $routerConfiguration = (new RouterConfiguration())
            ->setFallback('fallback');
        $routerConfiguration->add('foo', '/foo', 'action');

        $request = new Request(server: [
            'REQUEST_URI' => '/bar'
        ]);

        $this->expectException(NotFoundException::class);
        $routerConfiguration($request, '');
    }
}
