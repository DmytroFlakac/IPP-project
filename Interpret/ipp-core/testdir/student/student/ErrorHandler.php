<?php

namespace IPP\Student;

class ErrorHandler extends \IPP\Core\ReturnCode
{
    public static function ErrorMessage(int $code, string $message, int $order): void
    {
        if($order === -1)
            fwrite(STDERR, $message . PHP_EOL);
        else
            fwrite(STDERR, $message .  ": Instruction-> $order" . PHP_EOL);
        exit($code);
    }
}