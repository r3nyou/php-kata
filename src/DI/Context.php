<?php

namespace marcusjian\DI;

class Context
{
    /**
     * @var array<string,object>
     */
    private array $components = [];

    /**
     * @var array<string,object>
     */
    private array $componentImplementations = [];

    /**
     * @param string $type
     * @param object $instance
     *
     * @return void
     */
    public function bind(string $type, object $instance): void
    {
        $this->components[$type] = $instance;
    }

    public function bindInstance(string $type, string $implementation)
    {
        $this->componentImplementations[$type] = $implementation;
    }

    public function get(string $type): object
    {
        if (array_key_exists($type, $this->components)) {
            return $this->components[$type];
        }

        $implementation = $this->componentImplementations[$type];
        return new $implementation;
    }
}