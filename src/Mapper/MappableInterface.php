<?php

namespace Hyqo\Router\Mapper;

use Hyqo\Router\Route\Route;

interface MappableInterface
{
    /** @return \Generator<array-key,Route> */
    public function mapGenerator(): \Generator;
}
