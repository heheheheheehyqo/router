<?php

namespace Hyqo\Router\Matcher;

use Hyqo\Router\Route\Token;

/** @internal */
class MatchResult
{
    public function __construct(
        public string $string,
        /** @var Token[] */
        public array $tokens,
        public array $attributes,
        public array $matches,
    ) {
    }
}
