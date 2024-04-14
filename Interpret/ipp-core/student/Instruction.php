<?php

namespace IPP\Student;
use IPP\Core\ReturnCode;

class Instruction
{
    use InstructionDictionary;
    public string $opcode;
    public int $order;

    public int $executedOrder = 0;

    /** @var array<Argument> */
    public array $args;

    /** @var array<string> */
    private  $correctFrame = ["GF", "LF", "TF"];

    /**
     * @param string $opcode
     * @param int $order
     */
    function __construct($opcode, $order)
    {
        $this->opcode = $opcode;
        $this->order = $order;
        $this->args = [];
    }

    /**
     * @param string $type
     * @param string $value
     * @return void
     */
    public function addArgument($type, $value)
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
            if(!is_numeric($value))
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid argument value.", $this->order);
            else
                $value = (int)$value;
        elseif ($type === "bool")
            if($value !== "true" && $value !== "false")
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid argument value.", $this->order);
            else
                $value = $value === "true";
        elseif ($type === "nil")
            $value = null;

        $argument->value = $value;
        $this->args[] = $argument;
    }

        
  
    /**
     * @param array<Instruction> $instructions
     * @param Instruction $instruction
     * @return void
     */
    public static function addInstruction(&$instructions, $instruction)
    {
        $instructions[] = $instruction;
    } 

    /**
     * @return bool
     */
    public function isInstrCorrect()
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

    /**
     * @param array<Instruction> $Instructions
     * @return array<Instruction>
     */
    public static function SortByOrder($Instructions)
    {
        usort($Instructions, function ($a, $b) {
            return $a->order - $b->order;
        });
        
        $newOrder = 1;
        foreach ($Instructions as $instruction) {
            $instruction->executedOrder = $newOrder;
            $newOrder++;
        }
        return $Instructions;
    
    }

    /**
     * @param array<Instruction> $instructions
     * @return void
     */
    public static function PrintInstructions($instructions)
    {
        foreach ($instructions as $instruction) {
            echo "Opcode: {$instruction->opcode}, Order: {$instruction->order} ExecuteOrder: " . PHP_EOL;
            echo "Arguments:" . PHP_EOL;
            foreach ($instruction->args as $argument) {
                echo "  Type: {$argument->type}, Value: {$argument->value}" . PHP_EOL;
                if ($argument->type === "var") {
                    echo "    Frame: {$argument->frame}, Name: {$argument->name}" . PHP_EOL;
                }
            }
            echo PHP_EOL;
        }
    }
   
}