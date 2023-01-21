<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Route\Route;

interface MatchableInterface extends \IteratorAggregate
{
    public function __invoke(Request $request, string $base): ?Route;
}
