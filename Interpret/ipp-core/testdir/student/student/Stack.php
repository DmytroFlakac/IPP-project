<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

class Stack
{
    public mixed $stack;

    function __construct()
    {
        $this->stack = [];
    }

    public function push(mixed $value): void
    {
        $this->stack[] = $value;
    }

    public function pop(): mixed
    {
        if (count($this->stack) === 0)
            ErrorHandler::ErrorMessage(ReturnCode::VALUE_ERROR, "Stack is empty.", -1);
        return array_pop($this->stack);
    }

    function isEmpty(): bool
    {
        return count($this->stack) === 0;
    }
}