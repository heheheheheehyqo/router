<?php

namespace Hyqo\Router;

use Hyqo\Http\ContentType;
use Hyqo\Http\HttpCode;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Response\ForwardResponse;
use Hyqo\Router\Response\RedirectResponse;

/**
 * @param string[]|Closure|null $controller
 * @param array $attributes
 * @return ForwardResponse
 */
function forward($controller = null, array $attributes = []): ForwardResponse
{
    return new ForwardResponse($controller, $attributes);
}

function not_found()
{
    throw new NotFoundException();
}

function redirect(string $location = null, ?HttpCode $code = null): RedirectResponse
{
    return new RedirectResponse($code, $location);
}

function permanent_redirect(string $location = null): RedirectResponse
{
    return new RedirectResponse(HttpCode::MOVED_PERMANENTLY(), $location);
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
