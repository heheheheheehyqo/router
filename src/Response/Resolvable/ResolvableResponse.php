<?php

namespace Hyqo\Router\Response\Resolvable;

abstract class ResolvableResponse implements ResolvableInterface
{
    /** @var Resolvable */
    protected $resolvable = null;

    public function toRoute(string $name, array $attributes = []): self
    {
        $this->resolvable = new Resolvable($name, $attributes);

        return $this;
    }
}
