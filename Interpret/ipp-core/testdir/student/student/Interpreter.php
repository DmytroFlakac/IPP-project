<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;
use IPP\Core\FileInputReader;
use IPP\Core\ReturnCode;

class Interpreter extends AbstractInterpreter
{
    public function execute(): int {
        $dom = $this->source->getDOMDocument();
        $stdout = $this->stdout;
        $stdin = $this->input;
        $XmLValidator = new XMLProcessing($dom);
        $instructions = $XmLValidator->validate();
        new Program($instructions, $stdout, $stdin);
        return ReturnCode::OK;
    }
}
