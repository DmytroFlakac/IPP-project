<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

class Program
{
    private FrameManager $frameManager;
    private $labels;

    

    function __construct($instructions, $stdout, $stdin){
        $this->frameManager = new FrameManager();
        $this->labels = $this->findAllLabels($instructions);
        $this->executeInstructions($instructions, $this->frameManager, $this->labels, $stdout, $stdin);
    }

    
    private function findAllLabels($instructions): array
    {
        $labels = [];
        foreach ($instructions as $instruction) {
            if ($instruction->opcode === 'LABEL') {
                $label = $instruction->args[0]->value;
                if (array_key_exists($label, $labels)) {
                    ErrorHandler::ErrorMessage(ReturnCode::SEMANTIC_ERROR, "Duplicate label found.", $instruction->order);
                }
                $labels[$label] = $instruction->order-1;
            }
        }
        return $labels;
    }

    private function executeInstructions($instructions, $frameManager, $labels, $stdout, $stdin): void
    {
        $variableStack = new Stack();
        $pointerStack = new Stack();
        for ($i = 0; $i < count($instructions); $i++) {
            $instruction = $instructions[$i];
            if(Argument::isCALLorJUMP($instruction->opcode) && !array_key_exists($instruction->args[0]->value, $labels))
                ErrorHandler::ErrorMessage(ReturnCode::SEMANTIC_ERROR, "Undefined label.", $instruction->order);
            switch ($instruction->opcode) {
                case 'DEFVAR':
                    $frameManager->addVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name);
                    break;
                case 'MOVE':
                    $arg = Argument::getArgData($instruction->args[1], $frameManager);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg->value, $arg->type);
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
                    $pointerStack->push($i);
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'RETURN':
                    $i = $pointerStack->pop();
                    break;
                case 'JUMP':
                    $i = $labels[$instruction->args[0]->value];
                    break;
                case 'JUMPIFEQ':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== $arg2->type) {
                        if ($arg1->type === "nil" || $arg2->type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    if ($arg1->value === $arg2->value)
                        $i = $labels[$instruction->args[0]->value];
                    break;
                case 'JUMPIFNEQ':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== $arg2->type) {
                        if ($arg1->type === "nil" || $arg2->type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    if ($arg1->value !== $arg2->value)
                        $i = $labels[$instruction->args[0]->value];
                    break;
                case 'WRITE':
                    $arg = Argument::getArgData($instruction->args[0], $frameManager);
                    if ($arg->type === "nil" && $arg->value === null)
                        $stdout->writeString("");
                    elseif ($arg->type === "bool")
                        $stdout->writeBool($arg->value);
                    elseif ($arg->type === "int")
                        $stdout->writeInt($arg->value);
                    elseif ($arg->type === "string")
                        $stdout->writeString($arg->value);
                    else
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    break;
                case 'CONCAT':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "string" || $arg2->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value . $arg2->value, "string");
                    break;
                case 'READ':
                    $type = $instruction->args[1]->value;
                    $value = null;
                    if($type !== "int" && $type !== "bool" && $type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    elseif($type === "int")
                        $value = $stdin->readInt();
                    elseif($type === "bool")
                        $value = $stdin->readBool();
                    elseif($type === "string")
                        $value = $stdin->readString();
                    if($value === null && $type === "string")
                        $value = "";
                    elseif($value === null)
                        $type = "nil";
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $value, $type);
                    break;
                case 'STRLEN':
                    $arg = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, strlen($arg->value), "int");
                    break;
                case 'INT2CHAR':
                    $arg = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg->value < 0 || $arg->value > 1114112)
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid int value.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, chr($arg->value), "string");
                    break;
                case 'STRI2INT':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg1->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($arg2->value < 0 || $arg2->value >= strlen($arg1->value))
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid index.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, ord($arg1->value[$arg2->value]), "int");
                    break;
                case 'TYPE':
                    $arg = Argument::getArgData($instruction->args[1], $frameManager);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg->type, "string");
                    break;
                case 'ADD':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value + $arg2->value, "int");
                    break;
                case 'SUB':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value - $arg2->value, "int");
                    break;
                case 'MUL':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value * $arg2->value, "int");
                    break;
                case 'IDIV':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg2->value === 0)
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_VALUE_ERROR, "Division by zero.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, (int)($arg1->value / $arg2->value), "int");
                    break;
                case 'LT':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if(($arg1->type !== $arg2->type) || ($arg1->type === "nil" && $arg2->type === "nil"))
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value < $arg2->value, "bool");
                    break;
                case 'GT':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if(($arg1->type !== $arg2->type) || ($arg1->type === "nil" && $arg2->type === "nil"))
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value > $arg2->value, "bool");
                    break;
                case 'EQ':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== $arg2->type){
                        if ($arg1->type === "nil" || $arg2->type === "nil")
                            $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, "false", "bool");
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value === $arg2->value, "bool");
                    break;
                case 'AND':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "bool" || $arg2->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value && $arg2->value, "bool");
                    break;
                case 'OR':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg1->type !== "bool" || $arg2->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value || $arg2->value, "bool");
                    break;
                case 'NOT':
                    $arg = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, !$arg->value, "bool");
                    break;
                case 'GETCHAR':
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg1->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg2->value < 0 || $arg2->value >= strlen($arg1->value))
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid index.", $instruction->order);
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg1->value[$arg2->value], "string");
                    break;
                case 'SETCHAR':
                    $arg0 = Argument::getArgData($instruction->args[0], $frameManager);
                    if($arg0->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $arg1 = Argument::getArgData($instruction->args[1], $frameManager);
                    if($arg1->type !== "int" || $arg1->value < 0)
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $arg2 = Argument::getArgData($instruction->args[2], $frameManager);
                    if($arg2->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg1->value < 0 || $arg1->value >= strlen($arg0->value))
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid index.", $instruction->order);
                    $arg0->value[$arg1->value] = $arg2->value;
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $arg0->value, "string");
                    break;
                case 'DPRINT':
                    $arg = Argument::getArgData($instruction->args[0], $frameManager);
                    fwrite(STDERR, $arg->value . PHP_EOL);        
                    break;
                case 'BREAK':
                    fwrite(STDERR, "Instruction: " . $i . "\n");
                    fwrite(STDERR, "Global frame: " . json_encode($frameManager->globalFrame) . "\n");
                    fwrite(STDERR, "Local frames: " . json_encode($frameManager->localFrames) . "\n");
                    fwrite(STDERR, "Temporary frame: " . json_encode($frameManager->temporaryFrame) . "\n");
                    fwrite(STDERR, "Variable stack: " . json_encode($variableStack) . "\n");
                    break;
                case 'POPS':
                    $var = $variableStack->pop();
                    $frameManager->setVariable2Frame($instruction->args[0]->frame, $instruction->args[0]->name, $var->value, $var->type);
                    break;
                case 'PUSHS':
                    $arg = Argument::getArgData($instruction->args[0], $frameManager);
                    $clonedVar = clone $arg;
                    $variableStack->push($clonedVar);
                    break;
                case 'CLEARS':
                    $variableStack = new Stack();
                    break;
                case 'ADDS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "int";
                    $result->value = $arg1->value + $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'SUBS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "int";
                    $result->value = $arg1->value - $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'MULS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "int";
                    $result->value = $arg1->value * $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'IDIVS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== "int" || $arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg2->value === 0)
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_VALUE_ERROR, "Division by zero.", $instruction->order);
                    $result = new Variable();
                    $result->type = "int";
                    $result->value = (int)($arg1->value / $arg2->value);
                    $variableStack->push($result);
                    break;
                case 'LTS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if(($arg1->type !== $arg2->type) || ($arg1->type === "nil" && $arg2->type === "nil"))
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "bool";
                    $result->value = $arg1->value < $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'GTS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if(($arg1->type !== $arg2->type) || ($arg1->type === "nil" && $arg2->type === "nil"))
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "bool";
                    $result->value = $arg1->value > $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'EQS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== $arg2->type){
                        if ($arg1->type === "nil" || $arg2->type === "nil"){
                            $result = new Variable();
                            $result->type = "bool";
                            $result->value = false;
                            $variableStack->push($result);
                            break;
                        }
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    }
                    $result = new Variable();
                    $result->type = "bool";
                    $result->value = $arg1->value === $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'ANDS':
                    $arg1 = $variableStack->pop();
                    $arg2 = $variableStack->pop();
                    if($arg1->type !== "bool" || $arg2->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result =new Variable();
                    $result->type = "bool";
                    $result->value = $arg1->value && $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'ORS':
                    $arg1 = $variableStack->pop();
                    $arg2 = $variableStack->pop();
                    if($arg1->type !== "bool" || $arg2->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "bool";
                    $result->value = $arg1->value || $arg2->value;
                    $variableStack->push($result);
                    break;
                case 'NOTS':
                    $arg = $variableStack->pop();
                    if($arg->type !== "bool")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    $result = new Variable();
                    $result->type = "bool";
                    $result->value = !$arg->value;
                    $variableStack->push($result);
                    break;
                case 'INT2CHARS':
                    $arg = $variableStack->pop();
                    if($arg->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg->value < 0 || $arg->value > 1114112)
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid int value.", $instruction->order);
                    $result = new Variable();
                    $result->type = "string";
                    $result->value = chr($arg->value);
                    $variableStack->push($result);
                    break;
                case 'STRI2INTS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== "string")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);                   
                    if($arg2->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if($arg2->value < 0 || $arg2->value >= strlen($arg1->value))
                        ErrorHandler::ErrorMessage(ReturnCode::STRING_OPERATION_ERROR, "Invalid index.", $instruction->order);
                    $result = new Variable();
                    $result->type = "int";
                    $result->value = ord($arg1->value[$arg2->value]);
                    $variableStack->push($result);
                    break;
                case 'JUMPIFEQS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== $arg2->type) {
                        if ($arg1->type === "nil" || $arg2->type === "nil"){
                            break;
                        }
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);                    
                    }
                    if ($arg1->value === $arg2->value)
                        $i = $labels[$instruction->args[0]->value];
                    break;
                case 'JUMPIFNEQS':
                    $arg2 = $variableStack->pop();
                    $arg1 = $variableStack->pop();
                    if($arg1->type !== $arg2->type) {
                        if ($arg1->type === "nil" || $arg2->type === "nil"){
                            $i = $labels[$instruction->args[0]->value];
                            break;
                        }
                        else
                            ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);                    
                    }
                    if ($arg1->value !== $arg2->value)
                        $i = $labels[$instruction->args[0]->value];
                    break;
                case 'EXIT':
                    $arg = Argument::getArgData($instruction->args[0], $frameManager);
                    if($arg->type !== "int")
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_TYPE_ERROR, "Invalid type.", $instruction->order);
                    if ($arg->value < 0 || $arg->value > 49)
                        ErrorHandler::ErrorMessage(ReturnCode::OPERAND_VALUE_ERROR, "Invalid value.", $instruction->order);
                    exit($arg->value);
                }
            }
    }
}