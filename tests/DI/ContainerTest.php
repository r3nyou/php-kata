<?php

namespace Tests\DI;

use marcusjian\DI\ContextConfig;
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

    private ContextConfig $config;

    public function setUp(): void
    {
        $this->config = new ContextConfig();
    }

    /*
     * 1. component construction
     * TODO: instance
     */
    public function testShouldBindTypeToSpecificInstance()
    {
        $instance = new class implements Component{};
        $this->config->bindInstance(Component::class, $instance);

        $this->assertSame($instance, $this->config->getContext()->get(Component::class));
    }

    /*
     * 1. component construction
     * TODO: instance
     *   a. constructor
     *     1. no args
     */
    public function testShouldBindTypeToClass()
    {
        $this->config->bind(Component::class, ComponentWithDefaultConstruct::class);
        $instance = $this->config->getContext()->get(Component::class);

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

        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->config->bindInstance(Dependency::class, $dependency);

        /** @var ComponentWithInjectConstruct $instance */
        $instance = $this->config->getContext()->get(Component::class);
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

        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->config->bind(Dependency::class, DependencyWithInjectConstructor::class);
        $this->config->bindInstance(stdClass::class, $dependency);

        /** @var ComponentWithInjectConstruct $instance */
        $instance = $this->config->getContext()->get(Component::class);
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
        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);

        $this->expectException(DependencyNotFoundException::class);
        $this->expectExceptionMessage(
            'component: ' . Component::class .
            ',miss dependency: ' . Dependency::class
        );
        $this->config->getContext();
    }

    /*
     * sad path
     */
    public function testShouldThrowExceptionIfTransitiveDependenciesNotFound()
    {
        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->config->bind(Dependency::class, DependencyWithInjectConstructor::class);

        $this->expectException(DependencyNotFoundException::class);
        $this->expectExceptionMessage(
            'component: ' . Dependency::class .
            ',miss dependency: ' . stdClass::class
        );

        $this->config->getContext()->get(Component::class);
    }

    public function testShouldReturnNullIfComponentNotDefined()
    {
        $this->assertNull($this->config->getContext()->get(Component::class));
    }

    public function testShouldThrowExceptionIfCyclicDependenciesFound()
    {
        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->config->bind(Dependency::class, DependencyDependedOnComponent::class);

        try {
            $this->config->getContext()->get(Component::class);
        } catch (CyclicDependenciesException $e) {
            $this->assertNotFalse(strrpos($e->getMessage(), Component::class));
            $this->assertNotFalse(strrpos($e->getMessage(), Dependency::class));
            return;
        }

        $this->fail(CyclicDependenciesException::class . ' is not thrown');
    }

    public function testShouldThrowExceptionIfTransitiveCyclicDependenciesFound()
    {
        $this->config->bind(Component::class, ComponentWithInjectConstruct::class);
        $this->config->bind(Dependency::class, DependencyDependOnAnotherDependency::class);
        $this->config->bind(AnotherDependency::class, AnotherDependencyDependOnComponent::class);

        try {
            $contextConfig = $this->config;
            $contextConfig->getContext()->get(Component::class);
        } catch (CyclicDependenciesException $e) {
            $this->assertNotFalse(strrpos($e->getMessage(), Component::class));
            $this->assertNotFalse(strrpos($e->getMessage(), Component::class));
            $this->assertNotFalse(strrpos($e->getMessage(), AnotherDependency::class));
            return;
        }

        $this->fail(CyclicDependenciesException::class . ' is not thrown');
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