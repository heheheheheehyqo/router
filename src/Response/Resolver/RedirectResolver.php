<?php

namespace Hyqo\Router\Response\Resolver;

use Hyqo\Http\Header;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Service\UrlService;

class RedirectResolver implements ResolverInterface
{
    /**
     * @var UrlService
     */
    private $urlService;
    /**
     * @var Request
     */
    private $request;

    public function __construct(UrlService $urlService, Request $request)
    {
        $this->urlService = $urlService;
        $this->request = $request;
    }

    public function handleAnswer(array $attributes, ...$args): Response
    {
        [$code, $location] = $args;

        return (new Response($code))->setHeader(Header::LOCATION, $location);
    }

    public function handleRoute(array $attributes, Route $route): Response
    {
        $attributes = array_merge($this->request->attributes->all(), $attributes);

        $location = $this->urlService->buildRouteUrl($route, $attributes);

        return (new Response($attributes['_http_code']))->setHeader(Header::LOCATION, $location);
    }
}
