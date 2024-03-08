<?php

namespace IPP\Student;

/**
 * Trait to define a Variable
 */
trait Variable
{
    public string $type;
    public $value;
    function __construct()
    {
        $this->type = "undefined";
        $this->value = null;
    }
}

/**
 * Class Frame
 *
 * @property Variable[] $variables Array of variables of type Variable
 */
class Frame
{
    use Variable;

    /**
     * @var Variable[] Array to hold variables of type Variable
     */
    public $variables;

    public function __construct()
    {
        $this->variables = [];
    }

    public function addVariable($name): void
    {
        if (!array_key_exists($name, $this->variables)) {
            // Note: You need to instantiate a new Variable object here
            $this->variables[$name] = new class { use Variable; };
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Duplicate variable found.");
        }
    }
    public function getVariable($name)
    {
        if (array_key_exists($name, $this->variables)) {
            return $this->variables[$name];
        }
        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_ACCESS_UNDEFINED_VARIABLE, "Undefined variable.");
    }

    public function setVariable($name, $value, $type): void
    {
        if (array_key_exists($name, $this->variables)) {
            $this->variables[$name]->value = $value;
            $this->variables[$name]->type = $type;
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_ACCESS_UNDEFINED_VARIABLE, "Undefined variable.");
        }
    }
}
