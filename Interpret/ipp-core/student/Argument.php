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
}