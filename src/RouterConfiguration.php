<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Mapper\MappableInterface;
use Hyqo\Router\Route\Route;

class RouterConfiguration implements MappableInterface
{
    use Traits\FilterTrait;
    use Traits\PrefixTrait;
    use Traits\MiddlewareTrait;

    /** @var RouteConfiguration|GroupConfiguration[] */
    protected $routes = [];

    /** @var string|array|\Closure */
    protected $fallback = null;

    public function add(string $name, string $path): RouteConfiguration
    {
        return $this->routes[] = new RouteConfiguration($name, $path);
    }

    public function addGroup(?string $name = null): GroupConfiguration
    {
        return $this->routes[] = new GroupConfiguration($name);
    }

    /**
     * @param string|array|\Closure $controller
     */
    public function setFallback($controller): self
    {
        $this->fallback = $controller;

        return $this;
    }

    public function match(Request $request, string $base = ''): ?Route
    {
        if (
            !$this->isMethodMatch($request) ||
            !$this->isHostMatch($request) ||
            (null === $prefix = $this->matchPrefix($request, $base))
        ) {
            return null;
        }

        [$base, $tokens, $attributes] = $prefix;

        foreach ($this->routes as $configuration) {
            if ($route = $configuration->match($request, $base)) {
                return $route
                    ->withPatternPrefix($this->prefix)
                    ->withMiddlewares($this->getMiddlewares())
                    ->withTokens($tokens)
                    ->withAttributes($attributes)
                    ->withFallback($this->fallback);
            }
        }

        if ($this->fallback) {
            throw (new NotFoundException())->setController($this->fallback);
        }

        return null;
    }

    /** @inheritdoc */
    public function mapGenerator(): \Generator
    {
        $tokens = $this->collectTokens($this->prefix);

        foreach ($this->routes as $configuration) {
            foreach ($configuration->mapGenerator() as $route) {
                yield $route->getName() => $route
                    ->withPatternPrefix($this->prefix)
                    ->withTokens($tokens);
            }
        }
    }
}
