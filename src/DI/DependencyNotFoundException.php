<?php

namespace marcusjian\DI;

use Exception;

class DependencyNotFoundException extends Exception
{
    private string $componentType;

    private string $dependency;

    public function __construct(string $componentType, string $dependency)
    {
        $this->componentType = $componentType;
        $this->dependency = $dependency;
        $this->message = $this->getCustomMessage();
    }

    public function getCustomMessage(): string
    {
        return 'component: ' . $this->componentType .
            ',miss dependency: ' . $this->dependency;
    }
}