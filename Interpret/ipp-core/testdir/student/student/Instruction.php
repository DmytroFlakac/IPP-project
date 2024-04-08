<?php

namespace IPP\Student;
use IPP\Core\ReturnCode;

class Instruction
{
    public string $opcode;
    public int $order;
    public array $args;

    private array $correctFrame = ["GF", "LF", "TF"];

    function __construct($opcode, $order)
    {
        $this->opcode = $opcode;
        $this->order = $order;
        $this->args = [];
    }

    public function addArgument($type, $value): void
    {
        $argument = new Argument();
        $argument->type = $type;
        if ($type === "var") {
            $parts = explode("@", $value);
            $argument->frame = $parts[0];
            $argument->name = $parts[1];
            if (!in_array($argument->frame, $this->correctFrame))
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid frame.", $this->order);
        }
        elseif($type === "string")
            $value = $argument->decodeStringArgument($value);
        elseif ($type === "int")
            $value = (int)$value;
        elseif ($type === "bool")
            $value = $value === "true";
        elseif ($type === "nil")
            $value = null;

        $argument->value = $value;
        $this->args[] = $argument;
    }

        
    use InstructionDictionary;

    public static function addInstruction(&$instructions, $instruction): void
    {
        $instructions[] = $instruction;
    } 

    public function isInstrCorrect(): bool
    {
        if (!array_key_exists($this->opcode, self::$instructions))
            return false;
        $correctArgs = self::$instructions[$this->opcode];
        if (count($correctArgs) !== count($this->args))
            return false;
        for ($i = 0; $i < count($correctArgs); $i++) {              
            if (($correctArgs[$i] !== $this->args[$i]->type) &&
             !($correctArgs[$i] === "symb" && self::correctSymbol($this->args[$i]->type))) 
                return false;       
        }
        return true;
    }
   
}