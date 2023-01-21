<?php

namespace Hyqo\Router\Matcher;

use Hyqo\Router\Route\Token;

class Matcher
{
    public function __construct(
        protected string $pattern,
        /** @var Token[] */
        protected array $tokens,
    ) {
    }

    public function full(string $string): ?MatchResult
    {
        return $this->doMatch('^', '$', $string);
    }

    public function startsWith(string $string): ?MatchResult
    {
        return $this->doMatch('^', '', $string);
    }

    protected function doMatch(string $prefix, string $suffix, string $string): ?MatchResult
    {
        $pattern = "#$prefix$this->pattern$suffix#";

        $names = array_keys($this->tokens);

        $attributes = array_combine(
            $names,
            array_map(static function (Token $token) {
                return $token->getDefault();
            }, array_values($this->tokens))
        );

        if (@preg_match($pattern, $string, $matches)) {
            foreach ($names as $name) {
                if ($matches[$name] ?? null) {
                    $attributes[$name] = $matches[$name];
                }
            }

            return new MatchResult($matches[0], $this->tokens, $attributes, $matches);
        }

        return null;
    }
}
