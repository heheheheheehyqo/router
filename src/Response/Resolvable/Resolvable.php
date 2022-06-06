<?php

namespace Hyqo\Router\Response\Resolvable;

class Resolvable
{
    protected $name;

    protected $attributes;

    public function __construct(string $name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
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
