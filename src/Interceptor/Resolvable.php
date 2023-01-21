<?php

namespace Hyqo\Router\Interceptor;

class Resolvable
{
    public function __construct(
        protected string $name,
        protected array $attributes,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
