<?php

namespace Hyqo\Router\Exception;

class NotFoundException extends RouterException
{
    protected string|array|\Closure|null $controller = null;

    protected array $middlewares = [];

    public function setController(string|array|\Closure $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getController():string|array|\Closure|null
    {
        return $this->controller;
    }

    public function setMiddlewares(array $middlewares): static
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
