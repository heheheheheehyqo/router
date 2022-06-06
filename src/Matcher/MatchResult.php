<?php

namespace Hyqo\Router\Matcher;

use Hyqo\Router\Route\Token;

/** @internal */
class MatchResult
{
    /** @var string */
    public $string;

    /** @var Token[] */
    public $tokens;

    /** @var array */
    public $attributes;

    /** @var array */
    public $matches;

    public function __construct(
        string $string,
        array $tokens,
        array $values,
        array $matches
    ) {
        $this->string = $string;
        $this->tokens = $tokens;
        $this->attributes = $values;
        $this->matches = $matches;
    }
}
