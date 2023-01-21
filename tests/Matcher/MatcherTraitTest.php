<?php /** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test\Matcher;

use Hyqo\Http\Method;
use Hyqo\Http\Request;
use Hyqo\Router\Matcher\Matcher;
use Hyqo\Router\Matcher\MatcherTrait;
use PHPUnit\Framework\TestCase;

class MatcherTraitTest extends TestCase
{
    public function test_properties(): void
    {
        $filter = new class {
            use MatcherTrait;

            public function _getDefaults(): array
            {
                return $this->defaults;
            }

            public function _getRequirements(): array
            {
                return $this->requirements;
            }

            public function _getMethods(): array
            {
                return $this->methods;
            }

            public function _getHost(): string
            {
                return $this->host;
            }

            public function _getRequirement(string $name): string
            {
                return $this->getRequirement($name);
            }

            public function _matchMethodAndHost(Request $request): bool
            {
                return $this->matchMethodAndHost($request);
            }

            public function _matchMethod(Request $request): bool
            {
                return $this->matchMethod($request);
            }

            public function _matchHost(Request $request): bool
            {
                return $this->matchHost($request);
            }
        };

        $filter->setDefaults(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $filter->_getDefaults());

        $filter->setRequirements(['bar' => 'foo']);
        $this->assertEquals(['bar' => 'foo'], $filter->_getRequirements());
        $this->assertEquals('foo', $filter->_getRequirement('bar'));
        $this->assertEquals('[^/]+', $filter->_getRequirement('foo'));

        $this->assertTrue($filter->_matchMethod(new Request()));

        $filter->acceptMethod(Method::GET, Method::POST);
        $this->assertEquals([Method::GET, Method::POST], $filter->_getMethods());
        $this->assertTrue($filter->_matchMethod(new Request(server: ['REQUEST_METHOD' => 'GET'])));
        $this->assertFalse($filter->_matchMethod(new Request(server: ['REQUEST_METHOD' => 'PUT'])));

        $this->assertTrue($filter->_matchHost(new Request()));

        $filter->acceptHost('foo.com');
        $this->assertEquals('foo.com', $filter->_getHost());
        $this->assertTrue($filter->_matchHost(new Request(server: ['HTTP_HOST' => 'foo.com'])));
        $this->assertFalse($filter->_matchHost(new Request(server: ['HTTP_HOST' => 'bar.com'])));

        $this->assertTrue(
            $filter->_matchMethodAndHost(new Request(server: ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'foo.com']))
        );
    }

    public function test_collect_tokens(): void
    {
        $filter = new class {
            use MatcherTrait;

            public function _collectTokens(?string $string): array
            {
                return $this->collectTokens($string);
            }
        };

        $filter->setDefaults(['foo' => 'bar']);
        $this->assertEquals([], $filter->_collectTokens(null));

        $tokens = $filter->_collectTokens('/{foo}-{bar}');

        $this->assertArrayHasKey('foo', $tokens);
        $this->assertTrue($tokens['foo']->isOptional());

        $this->assertArrayHasKey('bar', $tokens);
        $this->assertFalse($tokens['bar']->isOptional());
    }

    public function test_matcher(): void
    {
        $filter = new class {
            use MatcherTrait;

            public function _buildMatcher(string $string): Matcher
            {
                return $this->buildMatcher($string);
            }
        };

        $filter->setDefaults(['foo' => 'bar']);

        $matcherA = $filter->_buildMatcher('{foo}');

        $matcherB = $filter->_buildMatcher('/{foo}');
        $matcherReflection = new \ReflectionClass(Matcher::class);

        $patternPropertyReflection = $matcherReflection->getProperty('pattern');
        $patternPropertyReflection->setAccessible(true);

        $tokensPropertyReflection = $matcherReflection->getProperty('tokens');
        $tokensPropertyReflection->setAccessible(true);

        $this->assertEquals('(?P<foo>[^/]+))?', $patternPropertyReflection->getValue($matcherA));
        $this->assertEquals('(?:/(?P<foo>[^/]+))?', $patternPropertyReflection->getValue($matcherB));
        $this->assertArrayHasKey('foo', $tokensPropertyReflection->getValue($matcherB));
    }

    public function test_prefix(): void
    {
        $object = new class {
            use MatcherTrait;

            public function _matchPrefix(Request $request, string $base): ?array
            {
                return $this->matchPrefix($request, $base);
            }
        };

        $request = new Request(server: ['REQUEST_URI' => '/sub/foo/bar']);

        $this->assertEquals(['/sub', [], []], $object->_matchPrefix($request, '/sub'));

        $object->setPrefix('/foo');

        $this->assertNull($object->_matchPrefix($request, 'bar'));

        $this->assertEquals(['/sub/foo', [], []], $object->_matchPrefix($request, '/sub'));
    }
}
