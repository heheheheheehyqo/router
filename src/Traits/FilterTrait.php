<?php

namespace Hyqo\Router\Traits;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\Matcher\Matcher;
use Hyqo\Router\Route\Token;

trait FilterTrait
{
    /** @var array */
    protected $defaults = [];

    /** @var array */
    protected $requirements = [];

    /** @var Method[]|null */
    protected $methods = null;

    /** @var string|null */
    protected $host = null;

    public function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function setRequirements(array $requirements): self
    {
        $this->requirements = $requirements;

        return $this;
    }

    public function acceptMethod(Method ...$methods): self
    {
        $this->methods = $methods;

        return $this;
    }

    public function acceptHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    protected function isOptional(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    protected function getRequirement(string $name): string
    {
        return $this->requirements[$name] ?? '[^/]+';
    }

    protected function isMethodMatch(Request $request): bool
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

    protected function isHostMatch(Request $request): bool
    {
        if (null === $this->host) {
            return true;
        }

//        echo sprintf("check host %s for %s\n", $this->host, $request->getHost());

        return null !== $this->buildMatcher($this->host)->full($request->getHost());
    }

    public function collectTokens(?string $string): array
    {
        if (null === $string) {
            return [];
        }

        $tokens = [];

        if (preg_match_all('/{(\w+)}/', $string, $matches)) {
            foreach ($matches[1] as $name) {
                $tokens[$name] = $token = new Token($name, $this->getRequirement($name));

                if ($this->isOptional($name)) {
                    $token->setOptional($this->defaults[$name] ?? null);
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
