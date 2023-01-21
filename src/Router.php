<?php

namespace Hyqo\Router;

use Hyqo\Container\Container;
use Hyqo\Http\HttpCode;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Exception\NotFoundException;
use Hyqo\Router\Exception\UndefinedRouteException;
use Hyqo\Router\Mapper\Mapper;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Service\CallableService;

class Router
{
    protected CallableService $callableService;

    protected ?Mapper $mapper = null;

    public function __construct(
        protected Container $container,
        protected RouterConfiguration $routerConfiguration
    ) {
        $this->callableService = $container->get(CallableService::class);
    }

    public function handle(Request $request): Response
    {
        $requestUri = $request->getRequestUri();

        if ($requestUri !== $sanitizedPathInfo = preg_replace([
                '#/{2,}#',
                '#(?<!^)/+(?=$)#',
                '#(?<!^)/+(?=\?)#',
            ], ['/', '', ''], $requestUri)) {
            return (new Response(HttpCode::MOVED_PERMANENTLY))
                ->setHeader('Location', $sanitizedPathInfo);
        }

        try {
            if ($route = ($this->routerConfiguration)($request)) {
                $request->attributes->add($route->getAttributes());

                $pipeline = $this->buildRoutePipeline($route);

                return wrap_to_response($pipeline($request));
            }
        } catch (NotFoundException $e) {
            if (null !== $fallback = $e->getController()) {
                $pipeline = $this->buildPipeline($e->getMiddlewares(), $fallback);

                return wrap_to_response($pipeline($request));
            }
        }

        throw new NotFoundException();
    }

    public function getRoute(string $name): Route
    {
        if (null === $this->mapper) {
            $this->mapper = new Mapper($this->routerConfiguration);
        }

        if (null !== $route = $this->mapper->getRoute($name)) {
            return $route;
        }

        throw new UndefinedRouteException(sprintf('Cannot find route "%s"', $name));
    }

    public function buildPipeline(array $middlewares, string|array|callable $controller, $fallback = null): Pipeline
    {
        $pipeline = new Pipeline($this->container, $this);

        foreach ($middlewares as $middlewareClassname) {
            $pipeline->pipe($this->container->make($middlewareClassname));
        }

        $callable = $this->callableService->makeCallable($controller);

        $pipeline->pipe(function () use ($callable, $fallback) {
            try {
                return $this->container->call($callable);
            } catch (NotFoundException $e) {
                if (null !== $fallback) {
                    $e->setController($fallback);
                }

                throw $e;
            }
        });

        return $pipeline;
    }

    public function buildRoutePipeline(Route $route): Pipeline
    {
        return $this->buildPipeline($route->getMiddlewares(), $route->getController(), $route->getFallback());
    }
}
