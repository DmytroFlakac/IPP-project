<?php

namespace IPP\Student;

trait InstructionDictionary
{
     /**
     * @var array<string, array<string>> Maps opcode names to their parameter types.
     */
    public static $instructions = [
        "MOVE" => ["var", "symb"],
        "CREATEFRAME" => [],
        "PUSHFRAME" => [],
        "POPFRAME" => [],
        "DEFVAR" => ["var"],
        "CALL" => ["label"],
        "RETURN" => [],
        "PUSHS" => ["symb"],
        "POPS" => ["var"],
        "ADD" => ["var", "symb", "symb"],
        "SUB" => ["var", "symb", "symb"],
        "MUL" => ["var", "symb", "symb"],
        "IDIV" => ["var", "symb", "symb"],
        "LT" => ["var", "symb", "symb"],
        "GT" => ["var", "symb", "symb"],
        "EQ" => ["var", "symb", "symb"],
        "AND" => ["var", "symb", "symb"],
        "OR" => ["var", "symb", "symb"],
        "NOT" => ["var", "symb"],
        "INT2CHAR" => ["var", "symb"],
        "STRI2INT" => ["var", "symb", "symb"],
        "READ" => ["var", "type"],
        "WRITE" => ["symb"],
        "CONCAT" => ["var", "symb", "symb"],
        "STRLEN" => ["var", "symb"],
        "GETCHAR" => ["var", "symb", "symb"],
        "SETCHAR" => ["var", "symb", "symb"],
        "TYPE" => ["var", "symb"],
        "LABEL" => ["label"],
        "JUMP" => ["label"],
        "JUMPIFEQ" => ["label", "symb", "symb"],
        "JUMPIFNEQ" => ["label", "symb", "symb"],
        "EXIT" => ["symb"],
        "DPRINT" => ["symb"],
        "BREAK" => [],
        "CLEARS" => [],
        "ADDS" => [],
        "SUBS" => [],
        "MULS" => [],
        "IDIVS" => [],
        "LTS" => [],
        "GTS" => [],
        "EQS" => [],
        "ANDS" => [],
        "ORS" => [],
        "NOTS" => [],
        "INT2CHARS" => [],
        "STRI2INTS" => [],
        "JUMPIFEQS" => ["label"],
        "JUMPIFNEQS" => ["label"],
    ];

    /**
     * @param string $type
     * @return bool
     */
    public static function correctSymbol($type)
    {
        return in_array($type, ["var", "int", "bool", "string", "nil"]);
    }

}