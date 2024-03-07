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
    public static function executeInstructions($instructions, $frameManager, $labels, $stdout, $stdin): void
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
                        $value = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $value = $instruction->args[1]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, $instruction->args[0]->type);
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
                case 'JUMPIFEQ':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.");
                    }
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    if ($symb1 === $symb2) {
                        $i = $labels[$instruction->args[0]->value];
                    }
                    break;
                case 'JUMPIFNEQ':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.");
                    }
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    if ($symb1 !== $symb2) {
                        $i = $labels[$instruction->args[0]->value];
                    }
                    break;
                case 'WRITE':
                    if ($instruction->args[0]->type === "var") {
                        $value = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name)->value;
                    } else {
                        $value = $instruction->args[0]->value;
                    }
                    $stdout->writeString($value);
                    break;
                case 'CONCAT':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 . $symb2, "string");
                    break;
                case 'READ':
                    $value = $stdin->readString();
                    if($instruction->args[1]->value === "int")
                        $value = (int)$value;
                    elseif($instruction->args[1]->value === "bool")
                        $value = $value === "true";
                    elseif ($instruction->args[1]->value === "nil")
                        $value = null;
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, $instruction->args[1]->value);
                    break;
                case 'STRLEN':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, strlen($value), "int");
                    break;
                case 'INT2CHAR':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, chr($value), "string");
                    break;
                case 'STRI2INT':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, ord($value[$index]), "int");
                    break;
                case 'TYPE':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type === "var")
                        $value = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->type;
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, "string");
                    break;
                case 'ADD':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 + $symb2, "int");
                    break;
                case 'SUB':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 - $symb2, "int");
                    break;
                case 'MUL':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 * $symb2, "int");
                    break;
                case 'IDIV':
$symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    if($symb2 === 0)
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Division by zero.");
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 / $symb2, "int");
                    break;
                case 'LT':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 < $symb2, "bool");
                    break;
                case 'GT':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 > $symb2, "bool");
                    break;
                case 'EQ':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 === $symb2, "bool");
                    break;
                case 'AND':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 && $symb2, "bool");
                    break;
                case 'OR':
                    $symb1 = null;
                    $symb2 = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb1 = $instruction->args[1]->value;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name)->value;
                    } else {
                        $symb2 = $instruction->args[2]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1 || $symb2, "bool");
                    break;
                case 'NOT':
                    $symb = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->value;
                    } else {
                        $symb = $instruction->args[1]->value;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, !$symb, "bool");
                    break;
                case 'GETCHAR':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value[$index], "string");
                    break;
                case 'SETCHAR':
                    if ($instruction->args[0]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[0]->value;
                        $type = $instruction->args[0]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $char = $var->value;
                        $type = $var->type;
                    } else {
                        $char = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid type.");
                    $value[$index] = $char;
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, "string");
                    break;
                case 'DPRINT':
                    if ($instruction->args[0]->type === "var") {
                        $value = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name)->value;
                    } else {
                        $value = $instruction->args[0]->value;
                    }
                    echo $value . "\n";
                    break;
                case 'BREAK':
                    echo "Instruction: " . $i . "\n";
                    echo "Global frame: " . json_encode($frameManager->globalFrame) . "\n";
                    echo "Local frames: " . json_encode($frameManager->localFrames) . "\n";
                    echo "Temporary frame: " . json_encode($frameManager->temporaryFrame) . "\n";
                    echo "Return pointer stack: " . json_encode(ReturnPointerStack::$stack) . "\n";
                    break;
                }

            }
        }
}
