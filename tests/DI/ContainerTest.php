<?php

namespace Tests\DI;

use marcusjian\DI\Component;
use marcusjian\DI\ComponentWithDefaultConstruct;
use marcusjian\DI\Context;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     1. no args
     *     2. with dependency
     *     3. A -> B -> C
     *   b. method
     * 2. dependency selection
     * 3. life cycle
     */

    /*
     * 1. component construction
     * TODO: instance
     */
    public function testShouldBindTypeToSpecificInstance()
    {
        $context = new Context();
        $instance = new class implements Component{};
        $context->bind(Component::class, $instance);

        $this->assertSame($instance, $context->get(Component::class));
    }

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     1. no args
     */
    public function testShouldBindTypeToClass()
    {
        $context = new Context();
        $context->bindInstance(Component::class, ComponentWithDefaultConstruct::class);
        $instance = $context->get(Component::class);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof Component);
    }
}
