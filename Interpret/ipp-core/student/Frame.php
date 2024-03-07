<?php

namespace IPP\Student;

class Frame
{
    public array $variables = [];

    public function __construct()
    {
        $this->variables = [];
    }

    public function addVariable($name, $value): void
    {
        $this->variables[$name] = $value;
    }

    public function getVariable($name)
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_ACCESS_UNDEFINED_VARIABLE, "Nedefinovaná premenná");
    }

    public function setVariable($name, $value): void
    {
        if (array_key_exists($name, $this->variables)) {
            $this->variables[$name] = $value;
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_ACCESS_UNDEFINED_VARIABLE, "Nedefinovaná premenná");
        }
    }
}

