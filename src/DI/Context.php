<?php

namespace marcusjian\DI;

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
    public function bind(string $type, object $instance): void
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

    public function bindInstance(string $type, string $implementation)
    {
        $this->providers[$type] = new class($implementation) implements Provider {
            public function __construct($implementation)
            {
                $this->implementation = $implementation;
            }

            public function get()
            {
                return new $this->implementation;
            }
        };
    }

    public function get(string $type): object
    {
        return $this->providers[$type]->get();
    }
}