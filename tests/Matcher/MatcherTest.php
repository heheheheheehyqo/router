<?php

namespace Hyqo\Router\Test\Matcher;

use Hyqo\Router\Matcher\Matcher;
use Hyqo\Router\Matcher\MatchResult;
use Hyqo\Router\Route\Token;
use PHPUnit\Framework\TestCase;

class MatcherTest extends TestCase
{
    public function test_match(): void
    {
        $matcher = new Matcher(
            '/(?P<foo>.*)/(?P<bar>\d+)',
            [
                'foo' => new Token('foo', '.*'),
                'bar' => new Token('bar', '\d+'),
            ],
        );

        $this->assertInstanceOf(MatchResult::class, $matcher->startsWith('/page/1/foo'));
        $this->assertNull($matcher->startsWith('/page'));

        $matchResult = $matcher->full('/page/1');
        $this->assertInstanceOf(MatchResult::class, $matchResult);
    }
}
