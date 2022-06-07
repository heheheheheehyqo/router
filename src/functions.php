<?php

namespace Hyqo\Router;

use Hyqo\Http\ContentType;
use Hyqo\Http\HttpCode;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Interceptor\ForwardInterceptor;
use Hyqo\Router\Interceptor\RedirectInterceptor;

/**
 * @param string|array|\Closure $controller
 * @param array $attributes
 */
function forward($controller, array $attributes = []): void
{
    $forwardInterceptor = new ForwardInterceptor();

    if (null !== $controller) {
        if (is_string($controller) && (0 === strpos($route = $controller, '@'))) {
            $forwardInterceptor->toRoute($route, $attributes);
        } else {
            $forwardInterceptor->setController($controller)->setAttributes($attributes);
        }
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

    if (0 === strpos($route = $location, '@')) {
        $redirectInterceptor->toRoute($route, $attributes);
    } else {
        $redirectInterceptor->setLocation($location)->setAttributes($attributes);
    }

    throw $redirectInterceptor;
}

function permanent_redirect(string $location, array $attributes = []): void
{
    redirect($location, $attributes, HttpCode::MOVED_PERMANENTLY());
}

function json_response(array $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK()))
        ->setContentType(ContentType::JSON)
        ->setContent(json_encode($content));
}

function html_response(string $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK()))
        ->setContentType(ContentType::HTML)
        ->setContent($content);
}

function text_response(string $content, ?HttpCode $code = null): Response
{
    return (new Response($code ?? HttpCode::OK()))
        ->setContentType(ContentType::TEXT)
        ->setContent($content);
}
