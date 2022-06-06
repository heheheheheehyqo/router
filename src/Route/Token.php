<?php

namespace Hyqo\Router\Route;

class Token
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $requirement;

    /**
     * @var bool
     */
    private $optional = false;

    /**
     * @var float|int|string|null
     */
    private $default = null;

    public function __construct(string $name, string $requirement)
    {
        $this->name = $name;
        $this->requirement = $requirement;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRequirement(): string
    {
        return $this->requirement;
    }

    /**
     * @return float|int|string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function setOptional($default): self
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
