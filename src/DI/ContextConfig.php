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

    public function getContext(): Context
    {
        // can check dependency here

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