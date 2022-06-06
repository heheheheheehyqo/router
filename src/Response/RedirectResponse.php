<?php

namespace Hyqo\Router\Response;

use Hyqo\Http\HttpCode;
use Hyqo\Router\Response\Resolvable\ResolvableResponse;
use Hyqo\Router\Response\Resolver\RedirectResolver;

class RedirectResponse extends ResolvableResponse
{
    /**
     * @var HttpCode|null
     */
    private $code;
    /**
     * @var string|null
     */
    private $location;

    public function __construct(?HttpCode $code = null, ?string $location = null)
    {
        $this->code = $code ?? HttpCode::FOUND();
        $this->location = $location;
    }

    public function getAttributes(): array
    {
        $attributes = ['_http_code' => $this->code];

        if (null !== $this->resolvable) {
            $attributes = array_merge_recursive($this->resolvable->getAttributes(), $attributes);
        }

        return $attributes;
    }

    public function getResolverClassname(): string
    {
        return RedirectResolver::class;
    }

    /**
     * @inheritDoc
     */
    public function getAnswer()
    {
        if (null !== $this->resolvable) {
            return $this->resolvable;
        }

        if (null !== $this->location) {
            return [$this->code, $this->location];
        }

        throw new \RuntimeException('redirect function should point to something');
    }

}
