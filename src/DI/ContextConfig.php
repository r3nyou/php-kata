<?php

namespace marcusjian\DI;

use ReflectionClass;
use ReflectionParameter;

class ContextConfig
{
    /**
     * @var array<string,Provider>
     */
    private array $providers = [];

    /**
     * @var array<string,string[]>
     */
    private array $dependencies = [];

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

        $this->dependencies[$type] = [];
    }

    public function bind(string $type, string $implementation)
    {
        $this->providers[$type] = new ConstructorInjectionProvider($type, $implementation, $this);

        $this->dependencies[$type] = array_map(function (ReflectionParameter $parameter) {
            return $parameter->getClass()->getName();
        }, (new ReflectionClass($implementation))->getConstructor()
            ->getParameters());
    }

    public function getContext(): Context
    {
        foreach ($this->dependencies as $component => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (!array_key_exists($dependency, $this->dependencies)) {
                    throw new DependencyNotFoundException($component, $dependency);
                }
            }
        }

        return new class($this) implements Context
        {
            private ContextConfig $config;

            public function __construct(ContextConfig $config)
            {
                $this->config = $config;
            }

            public function get(string $type): ?object
            {
                $provider = $this->config->getProvider($type);
                if (null === $provider) {
                    return null;
                }

                return $provider->get();
            }
        };
    }

    public function get(string $type): ?object
    {
        return $this->getContext()->get($type);
    }

    public function getProvider(string $type): ?Provider
    {
        return $this->providers[$type] ?? null;
    }
}

class ConstructorInjectionProvider implements Provider
{
    private string $componentType;

    private string $implementation;

    private ContextConfig $config;

    private bool $constructing = false;

    public function __construct(string $componentType, string $implementation, ContextConfig $config)
    {
        $this->componentType = $componentType;
        $this->implementation = $implementation;
        $this->config = $config;
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
                if (null === $this->config->getContext()->get($parameter->getClass()->getName())) {
                    throw new DependencyNotFoundException(
                        $this->componentType,
                        $parameter->getClass()->getName()
                    );
                }

                return $this->config->getContext()->get($parameter->getClass()->getName());
            }, $reflectionClass->getConstructor()->getParameters());

            return $reflectionClass->newInstanceArgs($dependencies);
        } catch (CyclicDependenciesException $e) {
            throw new CyclicDependenciesException($this->componentType, $e);
        } finally {
            $this->constructing = false;
        }
    }
}