<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

class Argument
{
    public string|null $type;
    public string|null $frame;
    public string|null $name;
    public string|null|bool|int $value;

    public function __construct()
    {
        $this->type = null;
        $this->frame = null;
        $this->name = null;
        $this->value = null;
    }
    public function checkType($type): void
    {
        if ($this->type !== $type) {
            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Nesprávny typ argumentu",-1);
        }
    }

    public function decodeStringArgument($str): array|string|null
    {
        // First, replace common escape sequences
        $replacements = [
            '\\n' => "\n", // New line
            '\\r' => "\r", // Carriage return
            '\\t' => "\t", // Tab
            '\\\\' => "\\", // Backslash
            // Add more common escape sequences as needed
        ];

        foreach ($replacements as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }

        return preg_replace_callback('/\\\\([0-9]{3})/', function($matches) {
            // Convert the decimal number to a character
            return chr((int)$matches[1]);
        }, $str);
    }

    public static function getArgData($arg, $frameManager) {
        if ($arg->type === "var") {
            $var = $frameManager->getFrameVariable($arg->frame, $arg->name);
        } else {
            $var = $arg;
        }
        return $var;
    }

    public static function isCALLorJUMP($opcode): bool
    {
        return $opcode === "CALL" || str_contains($opcode, "JUMP");
    }
}