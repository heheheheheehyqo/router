<?php

namespace Hyqo\Router\Service;

use Hyqo\Container\Container;
use Hyqo\Router\Exception\NotCallableException;

/** @internal */
class CallableService
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Closure|string[]|string $controller
     * @return callable
     */
    public function makeCallable($controller): callable
    {
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

        if (is_string($class = $controller)) {
            if (class_exists($class)) {
                $object = $this->container->make($class);

                if (is_callable($object)) {
                    return $object;
                }

                throw new NotCallableException(
                    sprintf('The class %s must have __invoke method', $class)
                );
            }

            throw new NotCallableException(
                sprintf('The class %s does not exist', $class)
            );
        }

        if (is_callable($controller)) {
            return $controller;
        }

        throw new NotCallableException(
            'The controller must be callable'
        );
    }
}
