<?php

namespace marcusjian\DI;

class Container
{
    private array $components = [];

    private array $componentTypes = [];

    /**
     * @param string $className
     * @param string|object $instance
     * @return void
     */
    public function bind(string $className, $instance): void
    {
        if (is_object($instance)) {
            $this->components[$className] = $instance;
        } else {
            $this->componentTypes[$className] = $instance;
        }
    }

    public function get(string $className): Object
    {
        if (key_exists($className, $this->components)) {
            return $this->components[$className];
        }

        $instance = $this->componentTypes[$className];
        return new $instance;
    }
}