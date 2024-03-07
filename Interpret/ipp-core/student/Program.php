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
                $labels[$label] = $instruction->order-1;
            }
        }
        return $labels;
    }
    public static function executeInstruction($instructions, $frameManager, $labels): void
    {
        for ($i = 0; $i < count($instructions); $i++) {
            $instruction = $instructions[$i];
            switch ($instruction->opcode) {
                case 'DEFVAR':
                    $frameManager->addVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name);
                    break;
                case 'MOVE':
                    $value = null;
                    if ($instruction->args[1]->type === "var") {
                        $value = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                    } else {
                        $value = $instruction->args[1]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value);
                    break;

                case 'CREATEFRAME':
                    $frameManager->createTemporaryFrame();
                    break;
                case 'PUSHFRAME':
                    $frameManager->pushTemporaryFrame();
                    break;
                case 'POPFRAME':
                    $frameManager->createTemporaryFrame();
                    $frameManager->popTemporaryFrame();
                    break;
                case 'CALL':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.");
                    }
                    ReturnPointerStack::push($i);
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'RETURN':
                    $i = ReturnPointerStack::pop();
                    break;
                case 'JUMP':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.");
                    }
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'WRITE':
                    if ($instruction->args[0]->type === "var") {
                        $value = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name);
                    } else {
                        $value = $instruction->args[0]->value;
                    }
                    echo $value;
                    break;
                    case "CONCAT":
                        $symb1 = null;
                        $symb2 = null;
                        if ($instruction->args[1]->type === "var") {
                            $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        } else {
                            $symb1 = $instruction->args[1]->value;
                        }
                        if ($instruction->args[2]->type === "var") {
                            $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        } else {
                            $symb2 = $instruction->args[2]->value;
                        }
                        $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 . $symb2);
                        break;


            }

            }
        }
}
