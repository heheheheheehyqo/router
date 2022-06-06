<?php

namespace Hyqo\Router\Response\Resolvable;

interface ResolvableInterface
{
    /**
     * @return string|string[]|\Closure|Resolvable
     * @internal
     */
    public function getAnswer();

    public function getResolverClassname(): string;

    public function getAttributes(): array;
}
