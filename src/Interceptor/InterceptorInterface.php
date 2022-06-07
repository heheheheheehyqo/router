<?php

namespace Hyqo\Router\Interceptor;

interface InterceptorInterface
{
    public function getHandler(): callable;
}
