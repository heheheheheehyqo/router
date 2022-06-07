<?php

namespace Hyqo\Router;

use Hyqo\Container\Container;
use Hyqo\Http\Request;
use Hyqo\Http\Response;
use Hyqo\Router\Interceptor\InterceptorInterface;

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

    protected function wrappedCall(Request $request, \Closure $fn): Response
    {
        try {
            $response = $fn();
        } catch (InterceptorInterface $interceptor) {
            $response = $this->wrappedCall($request, function () use ($interceptor, $request) {
                $handler = $interceptor->getHandler();
                return $this->container->call($handler, ['router' => $this->router, 'request' => $request]);
            });
        }

        return $response ?? new Response();
    }

    public function __invoke(Request $request): Response
    {
        if ($this->queue->isEmpty()) {
            return new Response();
        }

        $middleware = $this->queue->dequeue();

        return $this->wrappedCall($request, function () use ($middleware, $request) {
            return $middleware($request, $this);
        });
    }
}
