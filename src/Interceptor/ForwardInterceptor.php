<?php

namespace Hyqo\Router\Interceptor;

use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\ForwardException;
use Hyqo\Router\Router;
use Hyqo\Router\Service\CallableService;

use function Hyqo\Router\wrap_to_response;

class ForwardInterceptor extends BaseInterceptor
{
    protected string|array|\Closure|null $controller = null;

    protected array $attributes = [];

    public function toController(string|array|\Closure $controller, array $attributes = []): static
    {
        $this->controller = $controller;
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return callable(Router, Request, CallableService): Response
     */
    public function getHandler(): callable
    {
        return function (
            Router $router,
            Request $request,
            CallableService $callableService
        ): Response {
            if (null !== $this->resolvable) {
                return $this->handleResolvable($router, $request, $callableService);
            }

            if (null !== $this->controller) {
                return $this->handleController($request, $callableService);
            }

            throw new ForwardException('Forward should point to something');
        };
    }

    protected function handleResolvable(Router $router, Request $request, CallableService $callableService): Response
    {
        $route = $router->getRoute($this->resolvable->getName());

        $callable = $callableService->makeCallable($route->getController());

        $request->attributes->add($this->resolvable->getAttributes());

        return wrap_to_response($callableService->call($callable));
    }

    protected function handleController(Request $request, CallableService $callableService): Response
    {
        $callable = $callableService->makeCallable($this->controller);

        $request->attributes->add($this->attributes);

        return wrap_to_response($callableService->call($callable));
    }
}
