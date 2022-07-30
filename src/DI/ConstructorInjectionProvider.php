<?php

namespace marcusjian\DI;

use ReflectionClass;
use ReflectionParameter;

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
            if ("__construct" === $reflectionMethod->getName()) {
                $dependencies = array_map(function (ReflectionParameter $parameter) {
                    return $this->config->getContext()->get($parameter->getClass()->getName());
                }, $reflectionMethod->getParameters());

                $reflectionMethod->invoke($instance, ...$dependencies);
            }
        }

        foreach (array_reverse($reflectionClass->getMethods()) as $reflectionMethod) {
            if ("__construct" !== $reflectionMethod->getName()) {
                $dependencies = array_map(function (ReflectionParameter $parameter) {
                    return $this->config->getContext()->get($parameter->getClass()->getName());
                }, $reflectionMethod->getParameters());

                $reflectionMethod->invoke($instance, ...$dependencies);
            }
        }

        return $instance;
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        $reflectionClass = new ReflectionClass($this->implementation);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $dependencies = array_merge(
                $dependencies,
                array_map(function (ReflectionParameter $parameter) {
                    return $parameter->getClass()->getName();
                }, $reflectionMethod->getParameters())
            );
        }
        return $dependencies;
    }
}
