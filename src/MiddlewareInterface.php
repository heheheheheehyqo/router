<?php

namespace Hyqo\Router;

use Hyqo\Http\{Request};

interface MiddlewareInterface
{
    public function __invoke(Request $request, Pipeline $next);
}
