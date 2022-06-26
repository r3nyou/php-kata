<?php

namespace Tests\DI;

use marcusjian\DI\Component;
use marcusjian\DI\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    // TODO instance
    public function testBindTypeToSpecificInstance()
    {
        $container = new Container();
        $instance = new class implements Component{};
        $container->bind(Component::class, $instance);

        $this->assertSame($instance, $container->get(Component::class));
    }

    // TODO class default construct
}
