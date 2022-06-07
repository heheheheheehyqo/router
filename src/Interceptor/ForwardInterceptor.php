<?php

namespace Hyqo\Router\Interceptor;

use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Router;
use Hyqo\Router\Service\CallableService;

class ForwardInterceptor extends BaseInterceptor
{
    /**
     * @var string|array|\Closure
     */
    protected $controller;

    /**
     * @param array|\Closure|string $controller
     */
    public function setController($controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getHandler(): callable
    {
        return function (
            Router $router,
            Request $request,
            CallableService $callableService
        ) {
            if (null !== $this->resolvable) {
                return $this->handleResolvable($router, $request, $callableService);
            }

            if (null !== $this->controller) {
                return $this->handleController($request, $callableService);
            }

            throw new \RuntimeException('Forward should point to something');
        };
    }

    protected function handleResolvable(Router $router, Request $request, CallableService $callableService): Response
    {
        $route = $router->getRoute($this->resolvable->getName());
        $attributes = $this->resolvable->getAttributes();

        $callable = $callableService->makeCallable($route->getController());

        foreach ($attributes as $name => $value) {
            $request->attributes->set($name, $value);
        }

        return $callableService->call($callable) ?? new Response();
    }

    protected function handleController(Request $request, CallableService $callableService)
    {
        $callable = $callableService->makeCallable($this->controller);

        foreach ($this->attributes as $name => $value) {
            $request->attributes->set($name, $value);
        }

        return $callableService->call($callable) ?? new Response();
    }
}
