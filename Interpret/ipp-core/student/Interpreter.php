<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\NotImplementedException;



class Interpreter extends AbstractInterpreter
{
    use Program;
    public function execute(): int {
        $dom = $this->source->getDOMDocument();
        $stdout = $this->stdout;
        $stdin = $this->input;
        $XmLValidator = new XMLProcessing($dom);
        $instructions = $XmLValidator->validate();
        $labels = Interpreter::findAllLabels($instructions);
        $frameManager = new FrameManager();
        Interpreter::executeInstruction($instructions, $frameManager, $labels, $stdout, $stdin);

        return 0;
    }



}

