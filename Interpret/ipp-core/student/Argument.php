<?php

namespace IPP\Student;

class Argument
{
    public $type;
    public $frame = null;
    public $value;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
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
}