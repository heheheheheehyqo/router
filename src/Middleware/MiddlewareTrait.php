<?php

namespace Hyqo\Router\Middleware;

trait MiddlewareTrait
{
    /** @var string[] */
    protected array $middlewares = [];

    public function addMiddleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /** @param string[] $middlewares */
    public function addMiddlewares(string ...$middlewares): self
    {
        $this->middlewares = [...$this->middlewares, ...$middlewares];

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
