<?php

namespace Hyqo\Router\Response\Resolver;

use Hyqo\Http\Response;
use Hyqo\Router\Route\Route;

interface ResolverInterface
{
    public function handleAnswer(array $attributes, ...$args): Response;

    public function handleRoute(array $attributes, Route $route): Response;
}
