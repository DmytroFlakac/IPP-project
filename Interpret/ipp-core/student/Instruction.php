<?php

namespace IPP\Student;


class Instruction
{
    public string $opcode;
    public int $order;
    public ?array $args = null;

    function __construct($opcode, $order)
    {
        $this->opcode = $opcode;
        $this->order = $order;
        $this->args = [];
    }

    public function addArgument($type, $value): void
    {
        $argument = new Argument();
        if ($type === "var") {
            $parts = explode("@", $value);
            $argument->frame = $parts[0];
            $argument->name = $parts[1];
        }
        else{
            $argument->value = $value;
        }
        $argument->type = $type;
        $this->args[] = $argument;
    }

        
    use InstructionDictionary;

    public static function addInstruction(&$instructions, $instruction): void
    {
        $instructions[] = $instruction;
    }
}