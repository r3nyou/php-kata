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

            public function getDependencies(): array
            {
                return [];
            }
        };
    }

    public function bind(string $type, string $implementation)
    {
        $this->providers[$type] = ConstructorInjectionProvider::getConstructor($implementation, $this);
    }

    public function getContext(): Context
    {
        foreach ($this->providers as $component => $dependencies) {
            $this->checkDependencies($component, []);
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

    public function checkDependencies(string $component, array $visitStack)
    {
        $dependencies = $this->providers[$component]->getDependencies();
        foreach ($dependencies as $dependency) {
            if (!array_key_exists($dependency, $this->providers)) {
                throw new DependencyNotFoundException($component, $dependency);
            }
            if (in_array($dependency, $visitStack)) {
                throw CyclicDependenciesException::createFromArray($visitStack);
            }
            array_push($visitStack, $dependency);
            $this->checkDependencies($dependency, $visitStack);
            array_pop($visitStack);
        }
    }
}

class ConstructorInjectionProvider implements Provider
{
    private string $implementation;

    private ContextConfig $config;

    public function __construct(string $implementation, ContextConfig $config)
    {
        $this->implementation = $implementation;
        $this->config = $config;
    }

    public static function getConstructor(string $implementation, ContextConfig $config): ConstructorInjectionProvider {
        return new ConstructorInjectionProvider($implementation, $config);
    }

    public function get()
    {
        $reflectionClass = new ReflectionClass($this->implementation);

        $instance = $reflectionClass->newInstanceWithoutConstructor();

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $dependencies = array_map(function (ReflectionParameter $parameter) {
                return $this->config->getContext()->get($parameter->getClass()->getName());
            }, $reflectionMethod->getParameters());

            $reflectionMethod->invoke($instance, ...$dependencies);
        }

        return $instance;
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        $reflectionClass = new ReflectionClass($this->implementation);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $dependencies = array_merge($dependencies, array_map(function (ReflectionParameter $parameter) {
                return $parameter->getClass()->getName();
            }, $reflectionMethod->getParameters()));
        }

        return $dependencies;
    }
}