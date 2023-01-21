<?php

namespace Hyqo\Router\Matcher;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\Route\Token;
use JetBrains\PhpStorm\ArrayShape;

trait MatcherTrait
{
    protected ?string $prefix = null;

    protected array $defaults = [];

    protected array $requirements = [];

    /** @var Method[] */
    protected ?array $methods = null;

    protected ?string $host = null;

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function setDefaults(array $defaults): static
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function setRequirements(array $requirements): static
    {
        $this->requirements = $requirements;

        return $this;
    }

    public function acceptMethod(Method ...$methods): static
    {
        $this->methods = $methods;

        return $this;
    }

    public function acceptHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    protected function getRequirement(string $name): string
    {
        return $this->requirements[$name] ?? '[^/]+';
    }

    protected function matchMethodAndHost(Request $request): bool
    {
        return $this->matchMethod($request) && $this->matchHost($request);
    }

    protected function matchMethod(Request $request): bool
    {
        if (null === $this->methods) {
            return true;
        }

        foreach ($this->methods as $_method) {
            if ($request->getMethod()->value === $_method->value) {
                return true;
            }
        }

        return false;
    }

    protected function matchHost(Request $request): bool
    {
        if (null === $this->host) {
            return true;
        }

        return null !== $this->buildMatcher($this->host)->full($request->getHost());
    }

    #[ArrayShape(['string', 'array', 'array'])]
    protected function matchPrefix(Request $request, string $base): ?array
    {
        if (null === $this->prefix) {
            return [$base, [], []];
        }

        if (null === $match = $this->buildMatcher($base . $this->prefix)->startsWith($request->getPathInfo())) {
            return null;
        }

        return [$match->string, $match->tokens, $match->attributes];
    }

    /**
     * @return Token[]
     */
    protected function collectTokens(?string $string): array
    {
        if (null === $string) {
            return [];
        }

        $tokens = [];

        if (preg_match_all('/{(\w+)}/', $string, $matches)) {
            foreach ($matches[1] as $name) {
                $tokens[$name] = $token = new Token($name, $this->getRequirement($name));

                if (array_key_exists($name, $this->defaults)) {
                    $token->setOptional($this->defaults[$name]);
                }
            }
        }

        return $tokens;
    }

    protected function buildMatcher(string $string): Matcher
    {
        $tokens = $this->collectTokens($string);

        $pattern = preg_replace_callback(
            '/{(\w+)}/',
            static function (array $match) use ($tokens) {
                $name = $match[1];
                $token = $tokens[$name];

                $pattern = sprintf('(?P<%s>%s)', $name, $token->getRequirement());

                if ($token->isOptional()) {
                    $pattern = '##OPTIONAL##' . $pattern . ')?';
                }

                return $pattern;
            },
            $string
        );

        $pattern = preg_replace_callback(
            '/(.)?##OPTIONAL##/',
            static function (array $match) {
                if ($char = $match[1] ?? null) {
                    return sprintf('(?:%s', $char);
                }

                return '';
            },
            $pattern
        );

        return new Matcher($pattern, $tokens);
    }
}
