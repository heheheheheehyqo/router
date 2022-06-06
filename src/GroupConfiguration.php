<?php

namespace Hyqo\Router;

use Hyqo\Http\Request;
use Hyqo\Router\Mapper\MappableInterface;
use Hyqo\Router\Route\Route;

class GroupConfiguration implements MappableInterface
{
    use Traits\FilterTrait;
    use Traits\PrefixTrait;

    /** @var ?string */
    protected $name;

    /** @var bool */
    protected $configured = false;

    /** @var \Closure */
    protected $configurator;

    protected $routerConfiguration;

    public function __construct(?string $name)
    {
        $this->name = $name;
        $this->routerConfiguration = new RouterConfiguration();
    }

    public function setup(\Closure $configurator): self
    {
        $this->configurator = $configurator;

        return $this;
    }

    protected function configure(): void
    {
        if ($this->configured) {
            return;
        }

        ($this->configurator)($this->routerConfiguration);
        $this->configured = true;
    }

    public function match(Request $request, string $base = ''): ?Route
    {
        if (
            !$this->isMethodMatch($request) ||
            !$this->isHostMatch($request) ||
            (null === $prefix = $this->matchPrefix($request, $base))
        ) {
            return null;
        }

        [$base, $tokens, $attributes] = $prefix;

        $this->configure();

        if ($route = $this->routerConfiguration->match($request, $base)) {
            return $route
                ->withNamePrefix($this->name)
                ->withTokens($tokens)
                ->withAttributes($attributes)
                ->withPatternPrefix($this->prefix);
        }

        return null;
    }

    /** @inheritdoc */
    public function mapGenerator(): \Generator
    {
        $this->configure();

        foreach ($this->routerConfiguration->mapGenerator() as $route) {
            $route
                ->withNamePrefix($this->name)
                ->withPatternPrefix($this->prefix);

            yield $route->getName() => $route;
        }
    }
}
