<?php

namespace Hyqo\Router\Test\Route;

use Hyqo\Router\Exception\UndefinedControllerException;
use Hyqo\Router\Route\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    protected function createRoute(): Route
    {
        return new Route(
            name: 'r0',
            pathInfo: '/foo',
            pattern: 'bar',
            tokens: ['foo' => 'bar'],
            attributes: ['bar' => 'foo'],
            middlewares: ['middleware'],
            controller: 'controller'
        );
    }

    public function test_undefined_controller(): void
    {
        $this->expectException(UndefinedControllerException::class);
        new Route(
            name: 'r0',
            pathInfo: '/foo',
            pattern: 'bar',
            tokens: ['foo' => 'bar'],
            attributes: ['bar' => 'foo'],
            middlewares: ['middleware'],
            controller: null
        );
    }

    public function test_route(): void
    {
        $route = $this->createRoute();

        $this->assertEquals('r0', $route->getName());
        $this->assertEquals('/foo', $route->getPathInfo());
        $this->assertEquals('bar', $route->getPattern());
        $this->assertEquals(['foo' => 'bar'], $route->getTokens());
        $this->assertEquals(['bar' => 'foo'], $route->getAttributes());
        $this->assertEquals(['middleware'], $route->getMiddlewares());
        $this->assertEquals('controller', $route->getController());
        $this->assertNull($route->getFallback());
    }

    public function test_add_middlewares(): void
    {
        $route = $this->createRoute();

        $route->addMiddlewares([]);
        $this->assertEquals(['middleware'], $route->getMiddlewares());

        $route->addMiddlewares(['middleware2']);
        $this->assertEquals(['middleware2', 'middleware'], $route->getMiddlewares());
    }

    public function test_add_tokens(): void
    {
        $route = $this->createRoute();

        $route->addTokens([]);
        $this->assertEquals(['foo' => 'bar'], $route->getTokens());

        $route->addTokens(['foo' => 'bar2', 'bar' => 'baz']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $route->getTokens());
    }

    public function test_add_attributes(): void
    {
        $route = $this->createRoute();

        $route->addAttributes([]);
        $this->assertEquals(['bar' => 'foo'], $route->getAttributes());

        $route->addAttributes(['bar' => 'foo2', 'foo' => 'baz']);
        $this->assertEquals(['bar' => 'foo', 'foo' => 'baz'], $route->getAttributes());
    }

    public function test_add_pattern_prefix(): void
    {
        $route = $this->createRoute();

        $route->addPatternPrefix(null);
        $this->assertEquals('bar', $route->getPattern());

        $route->addPatternPrefix('foo');
        $this->assertEquals('foobar', $route->getPattern());
    }

    public function test_add_name_prefix(): void
    {
        $route = $this->createRoute();

        $route->addNamePrefix(null);
        $this->assertEquals('r0', $route->getName());

        $route->addNamePrefix('sub');
        $this->assertEquals('sub.r0', $route->getName());
    }

    public function test_add_fallback(): void
    {
        $route = $this->createRoute();

        $route->addFallback(null);
        $this->assertNull($route->getFallback());

        $route->addFallback('fallback');
        $this->assertEquals('fallback', $route->getFallback());
    }
}
