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
        if ($this->source->getAttribute('language') !== 'IPPcode23' || $this->source->nodeName !== 'program') {
            ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Incorrect XML format.", -1);
        }

        $instructions = [];
        if ($this->source->childNodes->length === 0) {
            // ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "No instructions found.", -1);
            exit(0);
        }

        
       $nonInstructionNodes = $this->xpath->query('/program/*[not(self::instruction)]');
       if ($nonInstructionNodes->length > 0) {
           ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Unexpected node found under program.", -1);
       }
        $instructionNodes = $this->xpath->query('/program/instruction');
        $lastOrder = 0;
        foreach ($instructionNodes as $instNode) {
            $order = intval($instNode->getAttribute('order'));
            if ($order < $lastOrder + 1) {
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Instruction order is not sequential.", -1);
            }
            $lastOrder = $order;
            $arguments = [];
            $opcode = strtoupper($instNode->getAttribute('opcode'));
            $instruction = new Instruction($opcode, $order);

            foreach ($instNode->childNodes as $argNode) {
                if ($argNode->nodeType === XML_ELEMENT_NODE && str_starts_with($argNode->nodeName, 'arg')) {
                    $argNum = intval(substr($argNode->nodeName, 3)); // Extract argument number from nodeName
                    if ($argNode->getAttribute('type') === '' || $argNode->nodeValue === null) {
                        ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument type or value is missing.", -1);
                    }
                    if ($argNum < 1 || $argNum > 3)
                        ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument number is out of range.", -1);
                    if(array_key_exists($argNum, $arguments))
                        ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Argument number is not unique.", -1);
                    $arguments[$argNum] = ['type' => $argNode->getAttribute('type'), 'value' => trim($argNode->nodeValue)];
                }
            }
        
            ksort($arguments);
            foreach ($arguments as $argNum => $argData) {
                $instruction->addArgument($argData['type'], $argData['value']);
            }
            if(!$instruction->isInstrCorrect())
                ErrorHandler::ErrorMessage(ReturnCode::INVALID_SOURCE_STRUCTURE, "Invalid instruction structure.", $order);
            Instruction::addInstruction($instructions, $instruction);
        }
        return $instructions;
    }
}