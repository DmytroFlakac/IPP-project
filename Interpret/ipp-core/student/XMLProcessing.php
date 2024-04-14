<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

class XMLProcessing
{
    private \DOMElement $source;
    private string $encoding;
    private string $xmlVersion;
    private \DOMXPath $xpath;

    function __construct(\DOMDocument $dom)
    {
        $this->source = $dom->documentElement;
        $this->source->normalize();
        $this->xpath = new \DOMXPath($dom);
        $this->encoding = $this->source->ownerDocument->xmlEncoding;
        $this->xmlVersion = $this->source->ownerDocument->xmlVersion;
    }

    public function validate(): mixed
    {
        if ($this->encoding !== 'UTF-8' || $this->xmlVersion !== '1.0') {
            ErrorHandler::ErrorMessage(ReturnCode::INVALID_XML_ERROR, "Incorrect XML format.", -1);
        }
        if ($this->source->getAttribute('language') !== 'IPPcode24' || $this->source->nodeName !== 'program') {
            ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Incorrect XML format.", -1);
        }

        $instructions = [];
        if ($this->source->childNodes->length === 0) {
            ErrorHandler::ErrorMessage(ReturnCode::OK, "Warning: Empty program.", -1);
        }

        
       $nonInstructionNodes = $this->xpath->query('/program/*[not(self::instruction)]');
       if ($nonInstructionNodes->length > 0) {
           ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Unexpected node found under program.", -1);
        }
        $instructionNodes = $this->xpath->query('/program/instruction');
        $lastOrder = 0;
        foreach ($instructionNodes as $instNode) {
            if($instNode instanceof \DOMElement){   
                $order = intval($instNode->getAttribute('order'));
                if ($order < 0 || $order === $lastOrder) {
                    ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Instruction order is not sequential.", $order);
                }
                $lastOrder = $order;
                $arguments = [];
                $opcode = strtoupper($instNode->getAttribute('opcode'));
                $instruction = new Instruction($opcode, $order);

                foreach ($instNode->childNodes as $argNode) {
                    if ($argNode instanceof \DOMElement && str_starts_with($argNode->nodeName, 'arg')) {
                        $argNum = intval(substr($argNode->nodeName, 3)); // Extract argument number from nodeName
                        if ($argNode->getAttribute('type') === '' || $argNode->nodeValue === null) {
                            ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument type or value is missing.", $order);
                        }
                        if ($argNum < 1 || $argNum > 3)
                            ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument number is out of range.", $order);
                        if(array_key_exists($argNum, $arguments))
                            ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument number is not unique.", $order);
                        $arguments[$argNum] = ['type' => $argNode->getAttribute('type'), 'value' => trim($argNode->nodeValue)];
                    }
                }
            
                ksort($arguments);
                $expectedArgNum = 1;
                foreach (array_keys($arguments) as $argNum) {
                    if ($argNum !== $expectedArgNum) {
                        ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Arguments must be in sorted order (arg1, arg2, arg3).", $order);
                    }
                    $expectedArgNum++;
                }
                foreach ($arguments as $argNum => $argData) {
                    $instruction->addArgument($argData['type'], $argData['value']);
                }
                if(!$instruction->isInstrCorrect())
                    ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid instruction structure.", $order);
                Instruction::addInstruction($instructions, $instruction);
            }   
            else
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid instruction structure.", -1);
        }
        return Instruction::SortByOrder($instructions);
    }
}