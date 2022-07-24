<?php

namespace marcusjian\DI;

use Exception;

class CyclicDependenciesException extends Exception
{
    /**
     * @var string[]
     */
    private array $components = [];

    public static function createFromArray(array $componentTypes)
    {
        $exception = new static('');
        $exception->components = $componentTypes;
        $exception->message = $exception->getCustomMessage();

        return $exception;
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