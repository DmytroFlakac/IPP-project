<?php

namespace IPP\Student;


class Instruction
{
    public string $opcode;
    public int $order;
    public array $args;

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
}