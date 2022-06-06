<?php

namespace Hyqo\Router\Route;

class Route
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $pattern;

    /** @var string */
    protected $pathInfo;

    /** @var Token[] */
    protected $tokens = [];

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $middlewares = [];

    /** @var string|array|Closure */
    protected $controller;

    /** @var string|array|Closure */
    protected $fallback;

    public function __construct(
        string $name,
        string $pathInfo,
        string $pattern,
        array $tokens,
        array $attributes,
        $middlewares,
        $controller
    ) {
        $this->name = $name;
        $this->pathInfo = $pathInfo;
        $this->pattern = $pattern;
        $this->tokens = $tokens;
        $this->attributes = $attributes;
        $this->middlewares = $middlewares;
        $this->controller = $controller;
        $this->fallback = null;
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

    /**
     * @return string|array|Closure
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string|array|Closure
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    public function withMiddlewares(array $middlewares): self
    {
        if (!count($middlewares)) {
            return $this;
        }

        foreach ($this->middlewares as $middleware) {
            $middlewares[] = $middleware;
        }

        $this->middlewares = $middlewares;

        return $this;
    }

    public function withTokens(array $tokens): self
    {
        if (!count($tokens)) {
            return $this;
        }

        foreach ($this->tokens as $name => $value) {
            $tokens[$name] = $value;
        }

        $this->tokens = $tokens;

        return $this;
    }

    public function withAttributes(array $attributes): self
    {
        if (!count($attributes)) {
            return $this;
        }

        foreach ($this->attributes as $name => $value) {
            $attributes[$name] = $value;
        }

        $this->attributes = $attributes;

        return $this;
    }

    public function withPatternPrefix(?string $pattern): self
    {
        if (null === $pattern) {
            return $this;
        }

        $this->pattern = $pattern . $this->pattern;

        return $this;
    }

    public function withNamePrefix(?string $name): self
    {
        if (null === $name) {
            return $this;
        }

        $this->name = $name . '.' . $this->name;

        return $this;
    }

    /**
     * @param string|array|Closure $fallback
     */
    public function withFallback($fallback): self
    {
        if (null === $fallback || null !== $this->fallback) {
            return $this;
        }

        $this->fallback = $fallback;

        return $this;
    }
}
