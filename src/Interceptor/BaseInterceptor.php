<?php

namespace Hyqo\Router\Interceptor;

abstract class BaseInterceptor extends \RuntimeException implements InterceptorInterface
{
    /** @var array */
    protected $attributes = [];

    /** @var Resolvable */
    protected $resolvable = null;

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function toRoute(string $name, array $attributes = []): void
    {
        $this->resolvable = new Resolvable($name, $attributes);
    }
}
