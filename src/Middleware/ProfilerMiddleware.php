<?php

namespace Hyqo\Router\Middleware;

use Hyqo\Measure\Measure;
use Hyqo\Router\Pipeline;
use Hyqo\Http\{Request, Response};

class ProfilerMiddleware implements \Hyqo\Router\MiddlewareInterface
{
    public function __invoke(Request $request, Pipeline $next): Response
    {
        Measure::start('et');

        $response = $next($request);

        $response->headers->set('X-Profiler-ET', Measure::stop('et'));

        return $response;
    }
}
