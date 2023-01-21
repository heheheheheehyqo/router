<?php

namespace Hyqo\Router;

use Hyqo\Http\HttpCode;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Interceptor\ForwardInterceptor;
use Hyqo\Router\Interceptor\RedirectInterceptor;
use JetBrains\PhpStorm\NoReturn;

function forward(string|array|\Closure $controller, array $attributes = []): void
{
    $forwardInterceptor = new ForwardInterceptor();

    if (is_string($controller) && (str_starts_with($route = $controller, '@'))) {
        $forwardInterceptor->toRoute($route, $attributes);
    } else {
        $forwardInterceptor->toController($controller, $attributes);
    }

    throw $forwardInterceptor;
}

function not_found()
{
    throw new NotFoundException();
}

function redirect(string $location, array $attributes = [], ?HttpCode $code = null): void
{
    $redirectInterceptor = (new RedirectInterceptor())->setHttpCode($code);

    if (str_starts_with($route = $location, '@')) {
        $redirectInterceptor->toRoute($route, $attributes);
    } else {
        $redirectInterceptor->toLocation($location);
    }

    throw $redirectInterceptor;
}

#[NoReturn]
function permanent_redirect(string $location, array $attributes = []): void
{
    redirect($location, $attributes, HttpCode::MOVED_PERMANENTLY);
}

function json_response(array $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK))
        ->setContentType('application/json')
        ->setContent(json_encode($content));
}

function html_response(string $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK))
        ->setContentType('text/html')
        ->setContent($content);
}

function text_response(string $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK))
        ->setContentType('text/plain')
        ->setContent($content);
}

function wrap_to_response(mixed $response): Response
{
    if ($response instanceof Response) {
        return $response;
    }

    if (null === $response) {
        return new Response();
    }

    return new Response(content: (string)$response);
}
