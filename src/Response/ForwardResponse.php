<?php

namespace Hyqo\Router\Response;

use Hyqo\Router\Response\Resolvable\ResolvableResponse;
use Hyqo\Router\Response\Resolver\ForwardResolver;

class ForwardResponse extends ResolvableResponse
{
    /** @var string|string[]|\Closure|null */
    protected $controller = null;

    protected $attributes = [];

    /**
     * @param string|string[]|\Closure|null $controller
     * @param array $attributes
     */
    public function __construct($controller = null, array $attributes = [])
    {
        $this->controller = $controller;
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        if (null !== $this->resolvable) {
            return $this->resolvable->getAttributes();
        }

        return $this->attributes;
    }

    public function getResolverClassname(): string
    {
        return ForwardResolver::class;
    }

    /**
     * @inheritDoc
     */
    public function getAnswer()
    {
        if (null !== $this->resolvable) {
            return $this->resolvable;
        }

        if (null !== $this->controller) {
            return $this->controller;
        }

        throw new \RuntimeException('forward function should point to something');
    }
}
