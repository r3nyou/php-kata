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