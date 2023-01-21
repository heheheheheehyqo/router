<?php

namespace Hyqo\Router\Route;

use Hyqo\Router\Exception\UndefinedControllerException;

class Route
{
    public function __construct(
        protected string $name,
        protected string $pathInfo,
        protected string $pattern,
        protected array $tokens,
        protected array $attributes,
        protected array $middlewares,
        protected string|array|\Closure|null $controller,
        protected string|array|\Closure|null $fallback = null,
    ) {
        if (null === $this->controller) {
            throw new UndefinedControllerException("Undefined controller for route '$this->name'");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPathInfo(): string
    {
        return $this->pathInfo;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getController(): string|array|\Closure
    {
        return $this->controller;
    }

    public function getFallback(): string|array|\Closure|null
    {
        return $this->fallback;
    }

    public function addMiddlewares(array $middlewares): static
    {
        if (!count($middlewares)) {
            return $this;
        }

        $this->middlewares = [...$middlewares, ...$this->middlewares];

        return $this;
    }

    public function addTokens(array $tokens): static
    {
        if (!count($tokens)) {
            return $this;
        }

        $this->tokens = [...$tokens, ...$this->tokens];

        return $this;
    }

    public function addAttributes(array $attributes): static
    {
        if (!count($attributes)) {
            return $this;
        }

        $this->attributes = [...$attributes, ...$this->attributes];

        return $this;
    }

    public function addPatternPrefix(?string $pattern): static
    {
        if (null === $pattern) {
            return $this;
        }

        $this->pattern = $pattern . $this->pattern;

        return $this;
    }

    public function addNamePrefix(?string $name): static
    {
        if (null === $name) {
            return $this;
        }

        $this->name = $name . '.' . $this->name;

        return $this;
    }

    public function addFallback(string|array|\Closure|null $fallback): static
    {
        if (null === $fallback || null !== $this->fallback) {
            return $this;
        }

        $this->fallback = $fallback;

        return $this;
    }
}
