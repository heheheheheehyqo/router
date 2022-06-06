<?php

namespace Hyqo\Router\Traits;

use Hyqo\Http\Request;

trait PrefixTrait
{
    /** @var string|null */
    protected $prefix;

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function matchPrefix(Request $request, string $base): ?array
    {
        if (null === $this->prefix) {
            return [$base, [], []];
        }
//            echo sprintf("check prefix %s for %s\n", $base . $this->prefix, $request->getPathInfo());

        if (null === $match = $this->buildMatcher($base . $this->prefix)->startsWith($request->getPathInfo())) {
            return null;
        }

        return [$match->string, $match->tokens, $match->attributes];
//            echo "set base $base\n";
    }
}
