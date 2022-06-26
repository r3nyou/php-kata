<?php

namespace Tests\DI;

use marcusjian\DI\Component;
use marcusjian\DI\ComponentWithDefaultConstruct;
use marcusjian\DI\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testShouldBindTypeToSpecificInstance()
    {
        $container = new Container();
        $instance = new class implements Component{};
        $container->bind(Component::class, $instance);

        $this->assertSame($instance, $container->get(Component::class));
    }

    // TODO class default construct
    public function testShouldBindTypeToClass()
    {
        $container = new Container();
        $container->bind(Component::class, ComponentWithDefaultConstruct::class);
        $instance = $container->get(Component::class);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof Component);
    }
}
