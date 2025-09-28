<?php

namespace MigrationOrderer\Exceptions;

use Exception;

class CircularDependencyException extends Exception
{
    protected array $cycle;

    public function __construct(array $cycle, string $message = "", int $code = 0, ?Exception $previous = null)
    {
        $this->cycle = $cycle;

        if (empty($message)) {
            $message = "Circular dependency detected: " . implode(' -> ', array_map('basename', $cycle));
        }

        parent::__construct($message, $code, $previous);
    }

    public function getCycle(): array
    {
        return $this->cycle;
    }
}