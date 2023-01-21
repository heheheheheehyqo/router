<?php

namespace Hyqo\Router\Interceptor;

abstract class BaseInterceptor extends \RuntimeException implements InterceptorInterface
{
    protected ?Resolvable $resolvable = null;

    public function toRoute(string $name, array $attributes = []): void
    {
        $this->resolvable = new Resolvable($name, $attributes);
    }
}
