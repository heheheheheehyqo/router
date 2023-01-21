<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Route\Route;

class GroupConfiguration implements MatchableInterface
{
    use Matcher\MatcherTrait;

    protected bool $configured = false;

    protected ?\Closure $configurator = null;

    protected RouterConfiguration $routerConfiguration;

    public function __construct(
        protected ?string $name = null,
    ) {
        $this->routerConfiguration = new RouterConfiguration();
    }

    public function setup(callable $configurator): static
    {
        $this->configurator = $configurator;

        return $this;
    }

    protected function configure(): void
    {
        if ($this->configured) {
            return;
        }

        if (null !== $this->configurator) {
            ($this->configurator)($this->routerConfiguration);
        }

        $this->configured = true;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function __invoke(Request $request, string $base = ''): ?Route
    {
        if (!$this->matchMethodAndHost($request)) {
            return null;
        }

        if (null === $prefix = $this->matchPrefix($request, $base)) {
            return null;
        }

        [$base, $tokens, $attributes] = $prefix;

        $this->configure();

        if ($route = ($this->routerConfiguration)($request, $base)) {
            return $route
                ->addNamePrefix($this->name)
                ->addTokens($tokens)
                ->addAttributes($attributes)
                ->addPatternPrefix($this->prefix);
        }

        return null;
    }

    /**
     * @return \Generator<string,Route>
     */
    public function getIterator(): \Generator
    {
        $this->configure();

        foreach ($this->routerConfiguration as $route) {
            $route
                ->addNamePrefix($this->name)
                ->addPatternPrefix($this->prefix);

            yield $route->getName() => $route;
        }
    }
}
