<?php

namespace Tests\DI;

use marcusjian\DI\Context;
use marcusjian\DI\CyclicDependenciesException;
use marcusjian\DI\DependencyNotFoundException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContainerTest extends TestCase
{
    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     1. no args
     *     2. with dependency
     *     3. A -> B -> C
     *     4. dependencies not found (sad path)
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

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     3. A -> B -> C
     */
    public function testShouldBindTypeToAClassWithTransitiveDependencies()
    {
        $dependency = new stdClass();

        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->context->bind(Dependency::class, DependencyWithInjectConstructor::class);
        $this->context->bindInstance(stdClass::class, $dependency);

        /** @var ComponentWithInjectConstruct $instance */
        $instance = $this->context->get(Component::class);
        $this->assertNotNull($instance);

        /** @var DependencyWithInjectConstructor $dependencyWithInjectConstructor */
        $dependencyWithInjectConstructor = $instance->getDependency();
        $this->assertNotNull($dependencyWithInjectConstructor);

        $this->assertSame($dependency, $dependencyWithInjectConstructor->getDependency());
    }

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     4. dependencies not found (sad path)
     */
    public function testShouldThrowExceptionIfDependencyNotFound()
    {
        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);

        $this->expectException(DependencyNotFoundException::class);
        $this->expectExceptionMessage(
            'component: ' . Component::class .
            ',miss dependency: ' . Dependency::class
        );
        $this->context->get(Component::class);
    }

    public function testShouldThrowExceptionIfTransitiveDependenciesNotFound()
    {
        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->context->bind(Dependency::class, DependencyWithInjectConstructor::class);

        $this->expectException(DependencyNotFoundException::class);
        $this->expectExceptionMessage(
            'component: ' . Dependency::class .
            ',miss dependency: ' . stdClass::class
        );

        $this->context->get(Component::class);
    }

    public function testShouldReturnNullIfComponentNotDefined()
    {
        $this->assertNull($this->context->get(Component::class));
    }

    public function testShouldThrowExceptionIfCyclicDependenciesFound()
    {
        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->context->bind(Dependency::class, DependencyDependedOnComponent::class);

        $this->expectException(CyclicDependenciesException::class);
        $this->context->get(Component::class);
    }

    public function testShouldThrowExceptionIfTransitiveCyclicDependenciesFound()
    {
        $this->context->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->context->bind(Dependency::class, DependencyDependOnAnotherDependency::class);
        $this->context->bind(AnotherDependency::class, AnotherDependencyDependOnComponent::class);

        $this->expectException(CyclicDependenciesException::class);
        $this->context->get(Component::class);
    }
}

interface Component
{

}

interface Dependency
{

}

interface AnotherDependency
{

}

class ComponentWithDefaultConstruct implements Component
{
    public function __construct()
    {

    }
}

class ComponentWithInjectConstruct implements Component
{
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

class DependencyWithInjectConstructor implements Dependency
{
    private stdClass $dependency;

    public function __construct(stdClass $dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): stdClass
    {
        return $this->dependency;
    }
}

class DependencyDependedOnComponent implements Dependency
{
    private Component $component;

    public function __construct(Component $component)
    {
        $this->component = $component;
    }
}

class AnotherDependencyDependOnComponent implements AnotherDependency
{
    private Component $component;

    public function __construct(Component $component)
    {
        $this->component = $component;
    }
}

class DependencyDependOnAnotherDependency implements Dependency
{
    private AnotherDependency $anotherDependency;

    public function __construct(AnotherDependency $anotherDependency)
    {
        $this->anotherDependency = $anotherDependency;
    }
}