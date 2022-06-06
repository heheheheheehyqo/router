<?php

namespace Hyqo\Router\Mapper;

use Hyqo\Router\Route\Route;

/** @internal */
class Mapper
{
    protected $generator;

    protected $cache = [];

    public function __construct(MappableInterface $mappable)
    {
        $this->generator = $mappable->mapGenerator();
    }

    public function getRoute(string $needed): ?Route
    {
        if (array_key_exists($needed, $this->cache)) {
            return $this->cache[$needed];
        }

        while ($this->generator->valid()) {
            $name = $this->generator->key();
            $routeConfiguration = $this->generator->current();

            $this->cache[$name] = $routeConfiguration;

            $this->generator->next();

            if ($name === $needed) {
                return $routeConfiguration;
            }
        }

        return null;
    }
}
