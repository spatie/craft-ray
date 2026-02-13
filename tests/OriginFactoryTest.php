<?php

namespace Spatie\CraftRay\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Spatie\Backtrace\Frame;
use Spatie\CraftRay\OriginFactory;
use Spatie\Ray\Origin\DefaultOriginFactory;

class OriginFactoryTest extends TestCase
{
    public function test_it_extends_default_origin_factory(): void
    {
        $this->assertTrue(
            is_subclass_of(OriginFactory::class, DefaultOriginFactory::class)
        );
    }

    public function test_search_method_is_not_overridden(): void
    {
        $method = new ReflectionMethod(OriginFactory::class, 'search');

        $this->assertSame(
            DefaultOriginFactory::class,
            $method->getDeclaringClass()->getName(),
            'OriginFactory should not override the search() method from DefaultOriginFactory'
        );
    }

    public function test_search_method_signature_matches_parent(): void
    {
        $parentMethod = new ReflectionMethod(DefaultOriginFactory::class, 'search');
        $childMethod = new ReflectionMethod(OriginFactory::class, 'search');

        $parentParams = $parentMethod->getParameters();
        $childParams = $childMethod->getParameters();

        $this->assertCount(count($parentParams), $childParams);

        foreach ($parentParams as $i => $parentParam) {
            $this->assertSame(
                $parentParam->getName(),
                $childParams[$i]->getName(),
                "Parameter {$i} name should match"
            );
        }
    }

    public function test_find_frame_for_event_returns_null_when_no_component_frame(): void
    {
        $factory = new OriginFactory();

        $frames = [
            new Frame('/some/file.php', 10, [], method: 'someMethod', class: 'SomeClass'),
            new Frame('/other/file.php', 20, [], method: 'otherMethod', class: 'OtherClass'),
        ];

        $method = new ReflectionMethod(OriginFactory::class, 'findFrameForEvent');
        $method->setAccessible(true);

        $result = $method->invoke($factory, $frames);

        $this->assertNull($result);
    }

    public function test_find_frame_for_event_returns_frame_after_component(): void
    {
        $factory = new OriginFactory();

        $frames = [
            new Frame('/some/file.php', 10, [], method: 'someMethod', class: 'SomeClass'),
            new Frame('/component/file.php', 20, [], method: 'trigger', class: 'yii\base\Component'),
            new Frame('/caller/file.php', 30, [], method: 'handleAction', class: 'App\MyController'),
        ];

        $method = new ReflectionMethod(OriginFactory::class, 'findFrameForEvent');
        $method->setAccessible(true);

        $result = $method->invoke($factory, $frames);

        $this->assertNotNull($result);
        $this->assertSame('/caller/file.php', $result->file);
        $this->assertSame(30, $result->lineNumber);
    }

    public function test_find_frame_for_event_returns_null_when_component_is_last_frame(): void
    {
        $factory = new OriginFactory();

        $frames = [
            new Frame('/some/file.php', 10, [], method: 'someMethod', class: 'SomeClass'),
            new Frame('/component/file.php', 20, [], method: 'trigger', class: 'yii\base\Component'),
        ];

        $method = new ReflectionMethod(OriginFactory::class, 'findFrameForEvent');
        $method->setAccessible(true);

        $result = $method->invoke($factory, $frames);

        $this->assertNull($result);
    }
}
