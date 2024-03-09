<?php

namespace IPP\Student;

use JetBrains\PhpStorm\NoReturn;

class ErrorHandler
{
    const SEMANTIC_ERROR = 52;
    const RUNTIME_TYPE_ERROR = 53;
    const RUNTIME_ACCESS_UNDEFINED_VARIABLE = 54;
    const RUNTIME_NONEXISTENT_FRAME = 55;
    const RUNTIME_MISSING_VALUE = 56;
    const RUNTIME_INVALID_VALUE = 57;
    const RUNTIME_STRING_MANIPULATION_ERROR = 58;
    const XML_FORMAT_ERROR = 31;
    const XML_UNEXPECTED_STRUCTURE = 32;
    const INTEGRATION_ERROR = 88;

    #[NoReturn] public static function ErrorMessage(int $code, string $message, int $order): void
    {
        fwrite(STDERR, $message .  ": line-> $order" . PHP_EOL);
        exit($code);
    }
}