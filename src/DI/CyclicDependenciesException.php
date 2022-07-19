<?php

namespace marcusjian\DI;

use Exception;

class CyclicDependenciesException extends Exception
{
    /**
     * @var string[]
     */
    private array $components = [];

    public function __construct(string $componentType, CyclicDependenciesException $e = null)
    {
        if (null !== $e) {
            $this->components = $e->getComponents();
        }
        $this->components[] = $componentType;
        $this->message = $this->getCustomMessage();
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    private function getCustomMessage(): string
    {
        return 'miss dependency: ' . implode(',', $this->getComponents());
    }
}