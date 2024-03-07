<?php

namespace IPP\Student;

class XMLProcessing
{
    private \DOMElement $source;
    private string $encoding;
    private string $xmlVersion;
    private \DOMXPath $xpath;

    function __construct(\DOMDocument $dom)
    {
        $this->source = $dom->documentElement;
        $this->xpath = new \DOMXPath($dom);
        $this->encoding = $this->source->ownerDocument->xmlEncoding;
        $this->xmlVersion = $this->source->ownerDocument->xmlVersion;
    }

    public function validate(): array
    {
        if ($this->encoding !== 'UTF-8' || $this->xmlVersion !== '1.0') {
            ErrorHandler::ErrorMessage(ErrorHandler::XML_FORMAT_ERROR, "Incorrect XML format.");
        }
        if ($this->source->getAttribute('language') !== 'IPPcode24' || $this->source->nodeName !== 'program') {
            ErrorHandler::ErrorMessage(ErrorHandler::XML_FORMAT_ERROR, "Incorrect XML format.");
        }

        $instructions = [];
        $lastOrder = 0;
        $nonInstructionNodes = $this->xpath->query('/program/*[not(self::instruction)]');
        if ($nonInstructionNodes->length > 0) {
            ErrorHandler::ErrorMessage(ErrorHandler::XML_UNEXPECTED_STRUCTURE, "Found non-instruction nodes directly under the program element.");
        }
        $instructionNodes = $this->xpath->query('/program/instruction');

        foreach ($instructionNodes as $instNode) {
            $order = intval($instNode->getAttribute('order'));
            if ($order !== $lastOrder + 1) {
                ErrorHandler::ErrorMessage(ErrorHandler::XML_UNEXPECTED_STRUCTURE, "Instruction order is not sequential.");
            }
            $lastOrder = $order;

            $opcode = strtoupper($instNode->getAttribute('opcode'));
            $instruction = new Instruction($opcode, $order);

            $argCount = 0;
            foreach ($instNode->childNodes as $argNode) {
                if ($argNode->nodeType === XML_ELEMENT_NODE && str_starts_with($argNode->nodeName, 'arg')) {
                    $argNum = intval(substr($argNode->nodeName, 3)); // Extracting the number from arg1, arg2, etc.
                    if ($argNum !== ++$argCount) { // Check if args are sequential
                        ErrorHandler::ErrorMessage(ErrorHandler::XML_FORMAT_ERROR, "Argument order is not sequential.");
                    }
                    $type = $argNode->getAttribute('type');
                    $value = $argNode->nodeValue;
                    $instruction->addArgument($type, $value);
                }
            }
            Instruction::addInstruction($instructions, $instruction);
        }
        if (empty($instructions)) {
            ErrorHandler::ErrorMessage(ErrorHandler::XML_UNEXPECTED_STRUCTURE, "No instructions found.");
        }

        return $instructions;
    }
}