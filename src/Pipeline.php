<?php

namespace Hyqo\Router;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Response\Resolvable\Resolvable;
use Hyqo\Router\Response\Resolvable\ResolvableResponse;
use Hyqo\Router\Response\Resolver\ResolverInterface;

class Pipeline
{
    /**
     * @var \SplQueue
     */
    protected $queue;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Container $container, Router $router)
    {
        $this->container = $container;
        $this->router = $router;
        $this->queue = new \SplQueue();
    }

    public function pipe(callable $callable): self
    {
        $this->queue->enqueue($callable);

        return $this;
    }

    public function __invoke(Request $request): Response
    {
        if ($this->queue->isEmpty()) {
            return new Response();
        }

        $middleware = $this->queue->dequeue();

        if (!$response = $middleware($request, $this)) {
            return new Response();
        }

        if ($response instanceof ResolvableResponse) {
            $resolvable = $response;
            /** @var ResolverInterface $resolver */
            $resolver = $this->container->make($resolvable->getResolverClassname());

            $answer = $response->getAnswer();

            if ($answer instanceof Resolvable) {
                $route = $this->router->getRoute($answer->getName());
                return $resolver->handleRoute($resolvable->getAttributes(), $route);
            }

            return $resolver->handleAnswer($resolvable->getAttributes(), $answer);
        }

        return $response;
    }
}
