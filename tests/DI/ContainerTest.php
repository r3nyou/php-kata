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

    private Context $context;

    public function setUp(): void
    {
        $this->context = new Context();
    }

    /*
     * 1. component construction
     * TODO: instance
     */
    public function testShouldBindTypeToSpecificInstance()
    {
        $instance = new class implements Component{};
        $this->context->bindInstance(Component::class, $instance);

        $this->assertSame($instance, $this->context->get(Component::class));
    }

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     1. no args
     */
    public function testShouldBindTypeToClass()
    {
        $this->context->bind(Component::class, ComponentWithDefaultConstruct::class);
        $instance = $this->context->get(Component::class);

        $this->assertNotNull($instance);
        $this->assertTrue($instance instanceof Component);
    }

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     2. with dependency
     */
    public function testShouldBindTypeToAClassWithInjectConstruction()
    {
        $dependency = new class implements Dependency{};

        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->context->bindInstance(Dependency::class, $dependency);

        /** @var ComponentWithInjectConstruct $instance */
        $instance = $this->context->get(Component::class);
        $this->assertNotNull($instance);
        $this->assertSame($dependency, $instance->getDependency());
    }
}

interface Dependency {

}

class ComponentWithInjectConstruct implements Component {
    private Dependency $dependency;

    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): Dependency
    {
        return $this->dependency;
    }
}