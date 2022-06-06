<?php

namespace Hyqo\Router\Exception;

class NotFoundException extends \RuntimeException
{
    /** @var string|array|\Closure */
    protected $controller;

    /**
     * @param string|array|\Closure $controller
     */
    public function setController($controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string|array|\Closure
     */
    public function getController()
    {
        return $this->controller;
    }
}
