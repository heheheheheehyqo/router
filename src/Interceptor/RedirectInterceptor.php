<?php

namespace Hyqo\Router\Interceptor;

use Hyqo\Http\HttpCode;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\RedirectException;
use Hyqo\Router\Router;
use Hyqo\Router\Service\UrlService;
use JetBrains\PhpStorm\ExpectedValues;

class RedirectInterceptor extends BaseInterceptor
{
    protected HttpCode $httpCode;

    protected ?string $location = null;

    public function setHttpCode(
        #[ExpectedValues(values: [HttpCode::MOVED_PERMANENTLY, HttpCode::FOUND])] ?HttpCode $httpCode
    ): self {
        $this->httpCode = $httpCode;

        return $this;
    }

    protected function getHttpCode(): HttpCode
    {
        return $this->httpCode ?? HttpCode::FOUND;
    }

    public function toLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return callable(Router, Request, UrlService): Response
     */
    public function getHandler(): callable
    {
        return function (
            Router $router,
            Request $request,
            UrlService $urlService
        ): Response {
            if (null !== $this->resolvable) {
                return $this->handleResolvable($router, $request, $urlService);
            }

            if (null !== $this->location) {
                return $this->handleLocation();
            }

            throw new RedirectException('Redirect should point to something');
        };
    }

    protected function handleResolvable(Router $router, Request $request, UrlService $urlService): Response
    {
        $route = $router->getRoute($this->resolvable->getName());
        $attributes = array_merge($request->attributes->all(), $this->resolvable->getAttributes());

        $location = $urlService->buildRouteUrl($route, $attributes);

        return (new Response($this->getHttpCode()))->setHeader('Location', $location);
    }

    protected function handleLocation(): Response
    {
        return (new Response($this->getHttpCode()))->setHeader('Location', $this->location);
    }
}
