<?php

namespace IPP\Student;

class Argument
{
    public $type;
    public $frame;
    public $name;
    public $value;

    public function __construct()
    {
        $this->type = null;
        $this->value = null;
        $this->frame = null;
        $this->name = null;
    }
    public function checkType($type): void
    {
        if ($this->type !== $type) {
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Nesprávny typ argumentu");
        }
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getType()
    {
        return $this->type;
    }

    public function decodeEscapeSequences($str) {
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

        // Then, handle the numeric escape sequences \xyz
        $str = preg_replace_callback('/\\\\([0-9]{3})/', function($matches) {
            // Convert the decimal number to a character
            return chr((int)$matches[1]);
        }, $str);

        return $str;
    }
}