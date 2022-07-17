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
        $provider = new class($implementation, $this) implements Provider {
            public function __construct($implementation, Context $context)
            {
                $this->implementation = $implementation;
                $this->context = $context;
            }

            public function get()
            {
                $reflectionClass = new ReflectionClass($this->implementation);

                $dependencies = array_map(function (ReflectionParameter $parameter) {
                    $context = $this->context;
                    if (null === $context->get($parameter->getClass()->getName())) {
                        throw new DependencyNotFoundException();
                    }

                    return $context->get($parameter->getClass()->getName());
                }, $reflectionClass->getConstructor()->getParameters());

                return $reflectionClass->newInstanceArgs($dependencies);
            }
        };

        $this->providers[$type] = $provider;
    }

    public function get(string $type): ?object
    {
        if (!array_key_exists($type, $this->providers)) {
            return null;
        }
        return $this->providers[$type]->get();
    }
}