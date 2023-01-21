<?php

namespace Hyqo\Router\Test\Service;

use Hyqo\Container\Container;
use Hyqo\Router\Exception\NotCallableException;
use Hyqo\Router\Service\CallableService;
use PHPUnit\Framework\TestCase;

class CallableServiceTest extends TestCase
{
    protected static object $service;

    public static function setUpBeforeClass(): void
    {
        $container = new Container();
        self::$service = $container->get(CallableService::class);
    }

    public function test_make_callable(): void
    {
        self::$service->makeCallable(static function () {
        });

        self::$service->makeCallable(
            new class () {
                public function __invoke(): void
                {
                }
            }
        );

        self::$service->makeCallable(
            [
                new class () {
                    public function foo(): void
                    {
                    }
                },
                'foo'
            ]
        );


        self::$service->makeCallable(Foo::class);
        self::$service->makeCallable([Bar::class, 'foo']);

        $this->expectException(NotCallableException::class);

        self::$service->makeCallable('foo');
        self::$service->makeCallable(Bar::class);
        self::$service->makeCallable([Bar::class, 'bar']);
    }

    /** @dataProvider provide_make_bad_callable_data */
    public function test_make_bad_callable(mixed $controller): void
    {
        $this->expectException(NotCallableException::class);

        self::$service->makeCallable($controller);
    }

    public function provide_make_bad_callable_data(): \Generator
    {
        yield ['foo'];
        yield [Bar::class];
        yield [[Bar::class]];
        yield [[Bar::class, 'bar']];
        yield [['FooBarClass', 'bar']];
    }
}


class Foo
{
    public function __invoke()
    {
    }
}

class Bar
{
    public function foo()
    {
    }
}
