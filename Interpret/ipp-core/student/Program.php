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
                    ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Duplicate label found.", $instruction->order);
                }
                $labels[$label] = $instruction->order-1;
            }
        }
        return $labels;
    }
    public static function executeInstructions($instructions, $frameManager, $labels, $stdout, $stdin): void
    {
        $variableStack = new Stack();
        $pointerStack = new Stack();
        for ($i = 0; $i < count($instructions); $i++) {
            $instruction = $instructions[$i];
            switch ($instruction->opcode) {
                case 'DEFVAR':
                    $frameManager->addVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name);
                    break;
                case 'MOVE':
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, $type);

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
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.", $instruction->order);
                    }
                    $pointerStack->push($i);
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'RETURN':
                    $i = $pointerStack->pop();
                    break;
                case 'JUMP':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.", $instruction->order);
                    }
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'JUMPIFEQ':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.", $instruction->order);
                    }
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== $symb2Type) {
                        if ($symb1Type === "nil" || $symb2Type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    if ($symb1Value === $symb2Value) {
                        $i = $labels[$instruction->args[0]->value];
                    }
                    break;
                case 'JUMPIFNEQ':
                    if(!array_key_exists($instruction->args[0]->value, $labels)){
                        ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Undefined label.", $instruction->order);
                    }
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== $symb2Type) {
                        if ($symb1Type === "nil" || $symb2Type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    if ($symb1Value !== $symb2Value) {
                        $i = $labels[$instruction->args[0]->value];
                    }
                    break;
                case 'WRITE':
                    if ($instruction->args[0]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[0]->value;
                        $type = $instruction->args[0]->type;
                    }

                    if ($type === "nil")
                        $value = "";
                    elseif ($type === "bool")
                        $value = $value ? "true" : "false";
                    if($value === null)
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_MISSING_VALUE, "Missing value.", $instruction->order);
                    $stdout->writeString($value);
                    break;
                case 'CONCAT':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "string" || $symb2Type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value . $symb2Value, "string");
                    break;
                case 'READ':
                    $value = $stdin->readString();
                    $type = $instruction->args[1]->value;
                    if($type === "int") {
                        if ($value > 1114112){
                            $value = null;
                            $type = "nil";
                        }
                        else
                            $value = (int)$value;
                    }
                    elseif($type === "bool") {
                        if ($value !== "true" && $value !== "false") {
                            $value = null;
                            $type = "nil";
                        }
                        else
                            $value = $value === "true";
                    }
                    elseif ($type === "nil")
                        $value = null;
                    elseif ($type === "string")
                        $value = (string)$value;

                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, $type);
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
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
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
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($value > 1114112)
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_STRING_MANIPULATION_ERROR, "Invalid int value.", $instruction->order);
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
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($index < 0 || $index >= strlen($value))
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_STRING_MANIPULATION_ERROR, "Invalid index.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, ord($value[$index]), "int");
                    break;
                case 'TYPE':
                    if ($instruction->args[1]->type === "var") {
                        $type = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name)->type;
                    } else {
                        $type = $instruction->args[1]->type;
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $type, "string");
                    break;
                case 'ADD':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "int" || $symb2Type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value + $symb2Value, "int");
                    break;
                case 'SUB':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "int" || $symb2Type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value - $symb2Value, "int");
                    break;
                case 'MUL':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "int" || $symb2Type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value * $symb2Value, "int");
                    break;
                case 'IDIV':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "int" || $symb2Type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($symb2Value === 0)
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_INVALID_VALUE, "Division by zero.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value / $symb2Value, "int");
                    break;
                case 'LT':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if(($symb1Type !== $symb2Type) || ($symb1Type === "nil" && $symb2Type === "nil"))
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value < $symb2Value, "bool");
                    break;
                case 'GT':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if(($symb1Type !== $symb2Type) || ($symb1Type === "nil" && $symb2Type === "nil"))
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value > $symb2Value, "bool");
                    break;
                case 'EQ':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== $symb2Type) {
                        if ($symb1Type === "nil" || $symb2Type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                        $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value == $symb2Value, "bool");
                    break;
                case 'AND':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "bool" || $symb2Type !== "bool")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value && $symb2Value, "bool");
                    break;
                case 'OR':
                    if ($instruction->args[1]->type === "var") {
                        $symb1 = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symb1Value = $symb1->value;
                        $symb1Type = $symb1->type;
                    } else {
                        $symb1Value = $instruction->args[1]->value;
                        $symb1Type = $instruction->args[1]->type;
                    }
                    if ($instruction->args[2]->type === "var") {
                        $symb2 = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $symb2Value = $symb2->value;
                        $symb2Type = $symb2->type;
                    } else {
                        $symb2Value = $instruction->args[2]->value;
                        $symb2Type = $instruction->args[2]->type;
                    }
                    if($symb1Type !== "bool" || $symb2Type !== "bool")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $symb1Value || $symb2Value, "bool");
                    break;
                case 'NOT':
                    $symb = null;
                    if ($instruction->args[1]->type === "var") {
                        $symb = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $symbType = $symb->type;
                        $symblValue = $symb->value;
                    } else {
                        $symblValue = $instruction->args[1]->value;
                        $symbType = $instruction->args[1]->type;
                    }
                    if($symbType !== "bool")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, !$symblValue, "bool");
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
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($index < 0 || $index >= strlen($value))
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_STRING_MANIPULATION_ERROR, "Invalid index.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value[$index], "string");
                    break;
                case 'SETCHAR':
                    if ($instruction->args[0]->type === "var") {
                        $value = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name)->value;
                    } else {
                        $value = $instruction->args[0]->value;
                    }
                    if ($instruction->args[1]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[1]->frame, $instruction->args[1]->name);
                        $index = $var->value;
                        $type = $var->type;
                    } else {
                        $index = $instruction->args[1]->value;
                        $type = $instruction->args[1]->type;
                    }
                    if($type !== "int" || $index < 0)
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($instruction->args[2]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[2]->frame, $instruction->args[2]->name);
                        $char = $var->value;
                        $type = $var->type;
                    } else {
                        $char = $instruction->args[2]->value;
                        $type = $instruction->args[2]->type;
                    }
                    if($type !== "string")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($index < 0 || $index >= strlen($value))
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_STRING_MANIPULATION_ERROR, "Invalid index.", $instruction->order);
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
//                    echo "Stack: " . json_encode(Stack::$stack) . "\n";
                    break;
                case 'POPS':
                    $var = $variableStack->pop();
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $var->value, $var->type);
                    break;
                case 'PUSHS':
                    if ($instruction->args[0]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name);
                    } else {
                        $var = $instruction->args[0];
                    }
                    if($var->type === "nil")
                        $var->value = "";
                    elseif($var->type === "bool")
                        $var->value = $var->value ? "true" : "false";
                    $clonedVar = clone $var;
                    $variableStack->push($clonedVar);
                    break;
                case 'EXIT':
                    if ($instruction->args[0]->type === "var") {
                        $var = $frameManager->getFrameVariable($instruction->args[0]->frame, $instruction->args[0]->name);
                        $value = $var->value;
                        $type = $var->type;
                    } else {
                        $value = $instruction->args[0]->value;
                        $type = $instruction->args[0]->type;
                    }
                    if($type !== "int")
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($value < 0 || $value > 9)
                        ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_INVALID_VALUE, "Invalid value.", $instruction->order);
                    exit($value);

                }

            }
        }
}
