<?php

namespace IPP\Student;

class ReturnPointerStack
{
    public static array $stack = [];

    public static function push($value): void
    {
        self::$stack[] = $value;
    }

    public static function pop()
    {
        if (count(self::$stack) === 0) {
            ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.");
        }
        return array_pop(self::$stack);
    }
}