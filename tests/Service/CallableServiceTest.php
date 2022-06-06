<?php

namespace Hyqo\Router\Test\Service;

use Hyqo\Container\Container;
use Hyqo\Router\Exception\NotCallableException;
use Hyqo\Router\Pipeline;
use Hyqo\Router\Service\CallableService;
use PHPUnit\Framework\TestCase;

class CallableServiceTest extends TestCase
{
    protected static $service;

    public static function setUpBeforeClass(): void
    {
        $container = new Container();
        self::$service = $container->get(CallableService::class);
    }

    public function test_make_callable()
    {
        self::$service->makeCallable(function () {
        });

        self::$service->makeCallable(
            new class () {
                public function __invoke()
                {
                }
            }
        );

        self::$service->makeCallable(
            [
                new class () {
                    public function foo()
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
