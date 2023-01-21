<?php

namespace Hyqo\Router\Test\Middleware;

use Hyqo\Router\Middleware\MiddlewareTrait;
use PHPUnit\Framework\TestCase;

class MiddlewareTraitTest extends TestCase
{
    public function test_middleware(): void
    {
        $object = new class {
            use MiddlewareTrait;
        };

        $this->assertEquals([], $object->getMiddlewares());

        $object->addMiddleware('foo');
        $this->assertEquals(['foo'], $object->getMiddlewares());

        $object->addMiddlewares('bar', 'baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $object->getMiddlewares());
    }
}
