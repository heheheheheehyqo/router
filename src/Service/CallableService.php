<?php

namespace Hyqo\Router\Service;

use Hyqo\Container\Container;
use Hyqo\Router\Exception\NotCallableException;

/** @internal */
class CallableService
{
    public function __construct(protected Container $container)
    {
    }

    public function makeCallable(string|array|callable $controller): callable
    {
        if (is_string($class = $controller)) {
            if (class_exists($class)) {
                $object = $this->container->make($class);

                if (is_callable($object)) {
                    return [$object, '__invoke'];
                }

                throw new NotCallableException(
                    sprintf('The class %s must have __invoke method', $class)
                );
            }

            throw new NotCallableException(
                sprintf('The class %s does not exist', $class)
            );
        }

        if (is_array($controller)) {
            if (count($controller) === 2) {
                [$class, $action] = $controller;

                if (is_object($class)) {
                    return [$class, $action];
                }

                if (class_exists($class)) {
                    $object = $this->container->make($class);

                    if (method_exists($object, $action)) {
                        return [$object, $action];
                    }

                    throw new NotCallableException(
                        sprintf('The method %s::%s does not exist', $class, $action)
                    );
                }

                throw new NotCallableException(
                    sprintf('The class %s does not exist', $class)
                );
            }

            throw new NotCallableException(
                'The controller must be an array [class, action] or a closure'
            );
        }

        return $controller;
    }

    /**
     * @codeCoverageIgnore
     */
    public function call(callable $callable, array $arguments = [])
    {
        return $this->container->call($callable, $arguments);
    }
}
