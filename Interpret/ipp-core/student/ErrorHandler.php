<?php

namespace IPP\Student;
use JetBrains\PhpStorm\NoReturn;

class ErrorHandler extends \IPP\Core\ReturnCode
{
    #[NoReturn] public static function ErrorMessage(int $code, string $message, int $order): void
    {
        fwrite(STDERR, $message .  ": line-> $order" . PHP_EOL);
        exit($code);
    }
}