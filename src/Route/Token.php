<?php

namespace Hyqo\Router\Route;

class Token
{
    protected bool $optional = false;

    protected float|int|string|null $default = null;

    public function __construct(
        protected string $name,
        protected string $requirement,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequirement(): string
    {
        return $this->requirement;
    }

    public function getDefault(): float|int|string|null
    {
        return $this->default;
    }

    public function setOptional(float|int|string|null $default): self
    {
        $this->optional = true;
        $this->default = $default;

        return $this;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }
}
