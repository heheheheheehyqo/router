<?php

namespace Hyqo\Router\Test\Mapper;

use Hyqo\Router\Mapper\MappableInterface;
use Hyqo\Router\Mapper\Mapper;
use Hyqo\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    public function test_cache(): void
    {
        $mappable = new class implements \IteratorAggregate {
            public function getIterator(): \Generator
            {
                yield 'r0' => new Route(
                    name: 'r0',
                    pathInfo: '/foo',
                    pattern: 'bar',
                    tokens: ['foo' => 'bar'],
                    attributes: ['bar' => 'foo'],
                    middlewares: ['middleware'],
                    controller: 'controller'
                );
            }
        };

        $mapper = new Mapper($mappable);
        $route = $mapper->getRoute('r0');
        $routeAgain = $mapper->getRoute('r0');

        $this->assertEquals('r0', $route->getName());
        $this->assertEquals('r0', $routeAgain->getName());
        $this->assertNull($mapper->getRoute('foo'));
    }
}
