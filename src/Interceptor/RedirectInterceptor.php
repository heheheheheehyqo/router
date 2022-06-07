<?php

namespace Hyqo\Router\Interceptor;

use Hyqo\Http\Header;
use Hyqo\Http\HttpCode;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Router;
use Hyqo\Router\Service\CallableService;
use Hyqo\Router\Service\UrlService;

class RedirectInterceptor extends BaseInterceptor
{
    /** @var HttpCode */
    protected $httpCode;

    /**
     * @var string
     */
    protected $location;

    public function setHttpCode(?HttpCode $httpCode): self
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    protected function getHttCode(): HttpCode
    {
        return $this->httpCode ?? HttpCode::FOUND();
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getHandler(): callable
    {
        return function (
            Router $router,
            Request $request,
            UrlService $urlService
        ) {
            if (null !== $this->resolvable) {
                return $this->handleResolvable($router, $request, $urlService);
            }

            if (null !== $this->location) {
                return $this->handleLocation();
            }

            throw new \RuntimeException('Redirect should point to something');
        };
    }

    protected function handleResolvable(Router $router, Request $request, UrlService $urlService): Response
    {
        $route = $router->getRoute($this->resolvable->getName());
        $attributes = array_merge($request->attributes->all(), $this->resolvable->getAttributes());

        $location = $urlService->buildRouteUrl($route, $attributes);

        return (new Response($this->getHttCode()))->setHeader(Header::LOCATION, $location);
    }

    protected function handleLocation(): Response
    {
        return (new Response($this->getHttCode()))->setHeader(Header::LOCATION, $this->location);
    }
}
