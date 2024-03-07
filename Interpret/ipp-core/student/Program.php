<?php

namespace IPP\Student;

trait Program
{
    public static function findAllLabels($instructions): array
    {
        $labels = [];
        foreach ($instructions as $instruction) {
            if ($instruction->opcode === 'LABEL') {
                $label = $instruction->args[0]->value;
                if (array_key_exists($label, $labels)) {
                    ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Duplicate label found.");
                }
                $labels[$label] = $instruction->order;
            }
        }
        return $labels;
    }


    public static function executeInstruction($instruction, $frameManager, $labels)
    {
        switch ($instruction->opcode) {
            case 'MOVE':
                $frameManager->setVariable($instruction->args[0]->value, $frameManager->getVariable($instruction->args[1]->value));
                break;
            case 'CREATEFRAME':
                $frameManager->createFrame();
                break;
            case 'PUSHFRAME':
                $frameManager->pushFrame();
                break;
            case 'POPFRAME':
                $frameManager->popFrame();
                break;
            case 'DEFVAR':
                $frameManager->defineVariable($instruction->args[0]->value);
                break;
            case 'CALL':
                $frameManager->call($instruction->args[0]->value);
                break;
            case 'RETURN':
                $frameManager->return();
                break;
            case 'PUSHS':
                $frameManager->pushStack($instruction->args[0]);
                break;
            case 'POPS':
                $frameManager->popStack($instruction->args[0]->value);
                break;
            case 'ADD':
                $frameManager->add($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'SUB':
                $frameManager->sub($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'MUL':
                $frameManager->mul($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'IDIV':
                $frameManager->idiv($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'LT':
                $frameManager->lt($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'GT':
                $frameManager->gt($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'EQ':
                $frameManager->eq($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;
            case 'AND':
                $frameManager->and($instruction->args[0], $instruction->args[1], $instruction->args[2]);
                break;

        }
    }

}