<?php

namespace Hyqo\Router\Response\Resolver;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Route\Route;
use Hyqo\Router\Service\CallableService;

class ForwardResolver implements ResolverInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var CallableService
     */
    private $callableService;
    /**
     * @var Request
     */
    private $request;

    public function __construct(
        Container $container,
        CallableService $callableService,
        Request $request
    ) {
        $this->container = $container;
        $this->callableService = $callableService;
        $this->request = $request;
    }

    public function handleAnswer(array $attributes, ...$args): Response
    {
        [$controller] = $args;

        $callable = $this->callableService->makeCallable($controller);

        foreach ($attributes as $name => $value) {
            $this->request->attributes->set($name, $value);
        }

        if ($response = $this->container->call($callable)) {
            return $response;
        }

        return new Response();
    }

    /**
     * @internal
     */
    public function handleRoute(array $attributes, Route $route): Response
    {
        $callable = $this->callableService->makeCallable($route->getController());

        foreach ($attributes as $name => $value) {
            $this->request->attributes->set($name, $value);
        }

        return $this->container->call($callable) ?? new Response();
    }
}
