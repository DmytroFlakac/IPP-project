<?php

namespace IPP\Student;

class Stack
{
    public array $stack = [];

    function __construct()
    {
        $this->stack = [];
    }

    public function push($value): void
    {
        $this->stack[] = $value;
    }

    public function pop()
    {
        if (count($this->stack) === 0)
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_MISSING_VALUE, "Stack is empty.", -1);
        return array_pop($this->stack);
    }

    function isEmpty(): bool
    {
        return count($this->stack) === 0;
    }
}