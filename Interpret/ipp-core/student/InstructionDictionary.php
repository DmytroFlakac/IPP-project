<?php

namespace IPP\Student;

trait InstructionDictionary
{
    public static array $instructions = [
        "MOVE" => ["var", "symb"],
        "CREATEFRAME" => [],
        "PUSHFRAME" => [],
        "POPFRAME" => [],
        "DEFVAR" => ["var"],
        "CALL" => ["label"],
        "RETURN" => [],
        "PUSHS" => ["symb"],
        "POPS" => ["var"],
        "ADD" => ["var", "symb1", "symb2"],
        "SUB" => ["var", "symb1", "symb2"],
        "MUL" => ["var", "symb1", "symb2"],
        "IDIV" => ["var", "symb1", "symb2"],
        "LT" => ["var", "symb1", "symb2"],
        "GT" => ["var", "symb1", "symb2"],
        "EQ" => ["var", "symb1", "symb2"],
        "AND" => ["var", "symb1", "symb2"],
        "OR" => ["var", "symb1", "symb2"],
        "NOT" => ["var", "symb"],
        "INT2CHAR" => ["var", "symb"],
        "STRI2INT" => ["var", "symb1", "symb2"],
        "READ" => ["var", "type"],
        "WRITE" => ["symb"],
        "CONCAT" => ["var", "symb1", "symb2"],
        "STRLEN" => ["var", "symb"],
        "GETCHAR" => ["var", "symb1", "symb2"],
        "SETCHAR" => ["var", "symb1", "symb2"],
        "TYPE" => ["var", "symb"],
        "LABEL" => ["label"],
        "JUMP" => ["label"],
        "JUMPIFEQ" => ["label", "symb1", "symb2"],
        "JUMPIFNEQ" => ["label", "symb1", "symb2"],
        "EXIT" => ["symb"],
        "DPRINT" => ["symb"],
        "BREAK" => []
    ];

    public static function getExpectedArguments(string $key)
    {
        if (array_key_exists($key, self::$instructions)) {
            return self::$instructions[$key];
        }
        return null;
    }
}