<?php /** @noinspection PhpExpressionResultUnusedInspection */

namespace Hyqo\Router\Test\Interceptor;

use Hyqo\Router\Interceptor\BaseInterceptor;
use Hyqo\Router\Interceptor\RedirectInterceptor;
use Hyqo\Router\Interceptor\Resolvable;
use PHPUnit\Framework\TestCase;

class BaseInterceptorTest extends TestCase
{
    protected function createInterceptor(): BaseInterceptor
    {
        return new class extends BaseInterceptor {
            public function getHandler(): callable
            {
                return static function () {
                };
            }
        };
    }

    public function test_resolvable(): void
    {
        $reflection = new \ReflectionClass(BaseInterceptor::class);
        $resolvablePropertyReflection = $reflection->getProperty('resolvable');
        $resolvablePropertyReflection->setAccessible(true);

        $interceptor = $this->createInterceptor();

        $this->assertNull($resolvablePropertyReflection->getValue($interceptor));

        $interceptor->toRoute('foo', ['bar' => 'baz']);
        $this->assertEquals(
            new Resolvable('foo', ['bar' => 'baz']),
            $resolvablePropertyReflection->getValue($interceptor)
        );
    }
}
