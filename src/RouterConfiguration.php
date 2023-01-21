<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Route\Route;
use Traversable;

class RouterConfiguration implements MatchableInterface
{
    use Matcher\MatcherTrait;
    use Middleware\MiddlewareTrait;

    protected ConfigurationCollection $configurations;

    protected string|array|\Closure|null $fallback = null;

    public function __construct()
    {
        $this->configurations = new ConfigurationCollection();
    }

    public function add(string $name, string $path, string|array|\Closure|null $controller = null): RouteConfiguration
    {
        $routeConfiguration = new RouteConfiguration($name, $path, $controller);

        $this->configurations->add($routeConfiguration);

        return $routeConfiguration;
    }

    public function addGroup(?string $name = null): GroupConfiguration
    {
        $groupConfiguration = new GroupConfiguration($name);

        $this->configurations->add($groupConfiguration);

        return $groupConfiguration;
    }

    public function setFallback(string|array|\Closure $controller): static
    {
        $this->fallback = $controller;

        return $this;
    }

    public function __invoke(Request $request, string $base = ''): ?Route
    {
        if (!$this->matchMethodAndHost($request)) {
            return null;
        }

        if (null === $prefix = $this->matchPrefix($request, $base)) {
            return null;
        }

        [$base, $tokens, $attributes] = $prefix;

        foreach ($this->configurations as $configuration) {
            if ($route = $configuration($request, $base)) {
                return $route
                    ->addPatternPrefix($this->prefix)
                    ->addMiddlewares($this->middlewares)
                    ->addTokens($tokens)
                    ->addAttributes($attributes)
                    ->addFallback($this->fallback);
            }
        }

        if ($this->fallback) {
            throw (new NotFoundException())
                ->setController($this->fallback)
                ->setMiddlewares($this->middlewares);
        }

        return null;
    }

    /**
     * @return \Generator<string,Route>
     */
    public function getIterator(): \Generator
    {
        $tokens = $this->collectTokens($this->prefix);

        foreach ($this->configurations as $configuration) {
            foreach ($configuration as $route) {
                yield $route->getName() => $route
                    ->addPatternPrefix($this->prefix)
                    ->addTokens($tokens);
            }
        }
    }
}
