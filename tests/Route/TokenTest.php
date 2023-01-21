<?php

namespace Hyqo\Router\Test\Route;

use Hyqo\Router\Route\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function test_token(): void
    {
        $token = new Token('foo', 'bar');

        $this->assertEquals('foo', $token->getName());
        $this->assertEquals('bar', $token->getRequirement());
        $this->assertNull($token->getDefault());
        $this->assertFalse($token->isOptional());

        $token->setOptional('baz');

        $this->assertEquals('baz', $token->getDefault());
        $this->assertTrue($token->isOptional());
    }
}
