<?php

namespace marcusjian\DI;

class Container
{
    /**
     * @var array<string, Provider>
     */
    private array $providers = [];

    /**
     * @param string $className
     * @param string|object $instance
     * @return void
     */
    public function bind(string $className, $instance): void
    {
        $this->providers[$className] = new class($instance) implements Provider
        {
            /** @var string|object */
            private $instance;

            public function __construct($instance)
            {
                $this->instance = $instance;
            }

            public function get()
            {
                return is_object($this->instance) ? $this->instance : (new $this->instance);
            }
        };
    }

    public function get(string $className): Object
    {
        return $this->providers[$className]->get();
    }
}