<?php

namespace Hyqo\Router\Traits;

trait MiddlewareTrait
{
    /** @var string[] */
    protected $middlewares = [];

    public function addMiddleware(string $middleware): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /** @param string[] $middlewares */
    public function addMiddlewares(string ...$middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
