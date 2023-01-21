<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Route\Route;

/** @internal */
class RouteConfiguration implements MatchableInterface
{
    use Matcher\MatcherTrait;
    use Middleware\MiddlewareTrait;

    protected array $attributes = [];

    public function __construct(
        protected string $name,
        protected string $path,
        protected string|array|\Closure|null $controller = null,
    ) {
    }

    public function setController(string|array|\Closure $controller): static
    {
        $this->controller = $controller;

        return $this;
    }

    public function __invoke(Request $request, string $base): ?Route
    {
        if (!$this->matchMethodAndHost($request)) {
            return null;
        }

        if (
            null !== $match = $this
                ->buildMatcher('(?P<pathInfo>' . $base . $this->path . ')/?')
                ->full($request->getPathInfo())
        ) {
            return new Route(
                $this->name,
                $match->matches['pathInfo'],
                $this->path,
                $match->tokens,
                $match->attributes,
                $this->middlewares,
                $this->controller
            );
        }

        return null;
    }

    /**
     * @return \Generator<string,Route>
     */
    public function getIterator(): \Generator
    {
        yield new Route(
            $this->name,
            '',
            $this->path,
            $this->collectTokens($this->path),
            [],
            [],
            $this->controller
        );
    }
}
