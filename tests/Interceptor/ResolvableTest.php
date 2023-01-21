<?php

namespace Hyqo\Router\Test\Interceptor;

use Hyqo\Router\Interceptor\Resolvable;
use PHPUnit\Framework\TestCase;

class ResolvableTest extends TestCase
{
    public function test_resolvable(): void
    {
        $resolvable = new Resolvable('foo', ['bar']);

        $this->assertEquals('foo', $resolvable->getName());
        $this->assertEquals(['bar'], $resolvable->getAttributes());
    }
}
