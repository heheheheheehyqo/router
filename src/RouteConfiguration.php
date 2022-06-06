<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Mapper\MappableInterface;
use Hyqo\Router\Route\Route;

/** @internal */
class RouteConfiguration implements MappableInterface
{
    use Traits\FilterTrait;
    use Traits\MiddlewareTrait;

    /** @var string */
    protected $name;

    /** @var string */
    protected $path;

    /** @var string|array|Closure */
    protected $controller;

    /** @var array */
    protected $attributes = [];

    public function __construct(string $name, string $path, $controller = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->controller = $controller;
    }

    /**
     * @param string|array|Closure $controller
     */
    public function setController($controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function match(Request $request, string $base): ?Route
    {
        if (!$this->isMethodMatch($request)) {
            return null;
        }

        if (!$this->isHostMatch($request)) {
            return null;
        }

//        echo sprintf("check %s %s for %s\n", $this->name, $base . $this->path, $request->getPathInfo());

        if (null !== $match = $this->buildMatcher('(?P<pathInfo>'.$base . $this->path.')/?')->full($request->getPathInfo())) {
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

    /** @inheritdoc */
    public function mapGenerator(): \Generator
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
