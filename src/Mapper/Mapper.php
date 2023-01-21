<?php

namespace Hyqo\Router\Mapper;

use Hyqo\Router\Route\Route;

/** @internal */
class Mapper
{
    /** @return \IteratorAggregate<array-key,Route> */
    protected \IteratorAggregate $generator;

    protected array $cache = [];

    public function __construct(\IteratorAggregate $mappable)
    {
        $this->generator = $mappable;
    }

    public function getRoute(string $needed): ?Route
    {
        if (array_key_exists($needed, $this->cache)) {
            return $this->cache[$needed];
        }

        foreach ($this->generator as $name=>$routeConfiguration){
            $this->cache[$name] = $routeConfiguration;

            if ($name === $needed) {
                return $routeConfiguration;
            }
        }

        return null;
    }
}
