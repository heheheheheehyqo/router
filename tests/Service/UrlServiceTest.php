<?php

namespace Hyqo\Router\Test\Service;

use Hyqo\Container\Container;
use Hyqo\Router\Exception\UrlBuilderException;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Route\Token;
use Hyqo\Router\Service\UrlService;
use PHPUnit\Framework\TestCase;

class UrlServiceTest extends TestCase
{

    protected static object $service;

    public static function setUpBeforeClass(): void
    {
        $container = new Container();
        self::$service = $container->get(UrlService::class);
    }

    /**
     * @dataProvider provide_build_url_data
     */
    public function test_build_url($name, $path, $tokens, $attributes, $expected): void
    {
        $route = new Route($name, '', $path, $tokens, [], [], []);

        $url = self::$service->buildRouteUrl($route, $attributes);

        $this->assertEquals($url, $expected);
    }

    public function provide_build_url_data(): \Generator
    {
        yield [
            'r1',
            '/{module}/{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => new Token('action', '.*'),
            ],
            [
                'module' => 'foo',
                'action' => 'bar',
            ],
            '/foo/bar'
        ];

        yield [
            'r2',
            '/{module}-{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => new Token('action', '\d*'),
            ],
            [
                'module' => 'foo',
                'action' => 123,
            ],
            '/foo-123'
        ];

        yield [
            'r3',
            '/{module}-{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => (new Token('action', '\d*'))->setOptional(123),
            ],
            [
                'module' => 'foo',
                'action' => 123,
            ],
            '/foo'
        ];

        yield [
            'r4',
            '/{module}-{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => (new Token('action', '\d*'))->setOptional(123),
            ],
            [
                'module' => 'foo',
                'action' => 1234,
            ],
            '/foo-1234'
        ];
    }

    /**
     * @dataProvider provide_build_url_exception_data
     */
    public function test_build_url_exception($name, $path, $tokens, $attributes): void
    {
        $route = new Route($name, '', $path, $tokens, [], [], []);

        $this->expectException(UrlBuilderException::class);
        self::$service->buildRouteUrl($route, $attributes);
    }

    public function provide_build_url_exception_data(): \Generator
    {
        yield [
            'r1',
            '/{module}/{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => new Token('action', '\d*'),
            ],
            [
                'module' => 'foo',
                'action' => 'bar',
            ],
        ];

        yield [
            'r1',
            '/{module}/{action}',
            [
                'module' => new Token('module', '.*'),
                'action' => new Token('action', '\d*'),
            ],
            [
            ],
        ];

        yield [
            'r2',
            '/{module}-{action}-{sub}',
            [
                'module' => new Token('module', '.*'),
                'action' => (new Token('action', '.*'))->setOptional(null),
                'sub' => (new Token('sub', '.*'))->setOptional(null),
            ],
            [
                'module' => 'foo',
                'sub' => 'bar'
            ],
        ];
    }
}
