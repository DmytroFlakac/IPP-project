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
    /** @var array<Variable> */
    public $variables;

    public function __construct()
    {
        $this->variables = [];
    }

    /**
     * @param string $name
     * @return void
     */
    public function addVariable($name)
    {
        if (!array_key_exists($name, $this->variables)) {
            // Note: You need to instantiate a new Variable object here
            $this->variables[$name] = new Variable();
        } 
        else 
            ErrorHandler::ErrorMessage(ReturnCode::SEMANTIC_ERROR, "Duplicate variable found.", -1);
        
    }

    /**
     * @param string $name
     * @return Variable
     */
    public function getVariable($name)
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        ErrorHandler::ErrorMessage(ReturnCode::VARIABLE_ACCESS_ERROR, "Undefined variable.", -1);
        return new Variable();
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public function setVariable($name, $value, $type)
    {
        if (array_key_exists($name, $this->variables)) {
            $this->variables[$name]->value = $value;
            $this->variables[$name]->type = $type;
        } 
        else 
            ErrorHandler::ErrorMessage(ReturnCode::VARIABLE_ACCESS_ERROR, "Undefined variable.", -1);
        
    }
}
