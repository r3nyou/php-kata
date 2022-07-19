<?php

namespace marcusjian\DI;

use ReflectionClass;
use ReflectionParameter;

class Context
{
    /**
     * @var array<string,Provider>
     */
    private array $providers = [];

    /**
     * @param string $type
     * @param object $instance
     *
     * @return void
     */
    public function bindInstance(string $type, object $instance): void
    {
        $this->providers[$type] = new class($instance) implements Provider {
            public function __construct($instance)
            {
                $this->instance = $instance;
            }

            public function get()
            {
                return $this->instance;
            }
        };
    }

    public function bind(string $type, string $implementation)
    {
        $this->providers[$type] = new ConstructorInjectionProvider($type, $implementation, $this);
    }

    public function get(string $type): ?object
    {
        if (!array_key_exists($type, $this->providers)) {
            return null;
        }
        return $this->providers[$type]->get();
    }
}

class ConstructorInjectionProvider implements Provider
{
    private string $componentType;

    private string $implementation;

    private Context $context;

    private bool $constructing = false;

    public function __construct(string $componentType, string $implementation, Context $context)
    {
        $this->componentType = $componentType;
        $this->implementation = $implementation;
        $this->context = $context;
    }

    public function get()
    {
        if ($this->constructing) {
            throw new CyclicDependenciesException($this->componentType);
        }

        try {
            $this->constructing = true;

            $reflectionClass = new ReflectionClass($this->implementation);

            $dependencies = array_map(function (ReflectionParameter $parameter) {
                if (null === $this->context->get($parameter->getClass()->getName())) {
                    throw new DependencyNotFoundException(
                        $this->componentType,
                        $parameter->getClass()->getName()
                    );
                }

                return $this->context->get($parameter->getClass()->getName());
            }, $reflectionClass->getConstructor()->getParameters());

            return $reflectionClass->newInstanceArgs($dependencies);
        } catch (CyclicDependenciesException $e) {
            throw new CyclicDependenciesException($this->componentType, $e);
        } finally {
            $this->constructing = false;
        }
    }
}