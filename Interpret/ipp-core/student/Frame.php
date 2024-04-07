<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

/**
 * Class Frame
 *
 * @property Variable[] $variables Array of variables of type Variable
 */
class Frame
{
    public array $variables;

    public function __construct()
    {
        $this->variables = [];
    }

    public function addVariable($name): void
    {
        if (!array_key_exists($name, $this->variables)) {
            // Note: You need to instantiate a new Variable object here
            $this->variables[$name] = new Variable();
        } 
        else 
            ErrorHandler::ErrorMessage(ReturnCode::SEMANTIC_ERROR, "Duplicate variable found.", -1);
        
    }
    public function getVariable($name): Variable
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        ErrorHandler::ErrorMessage(ReturnCode::VARIABLE_ACCESS_ERROR, "Undefined variable.", -1);
    }

    public function setVariable($name, $value, $type): void
    {
        if (array_key_exists($name, $this->variables)) {
            $this->variables[$name]->value = $value;
            $this->variables[$name]->type = $type;
        } 
        else 
            ErrorHandler::ErrorMessage(ReturnCode::VARIABLE_ACCESS_ERROR, "Undefined variable.", -1);
        
    }
}
