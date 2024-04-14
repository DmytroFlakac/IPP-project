foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'instruction') {
                $order = trim($child->getAttribute('order'));
                $opcode = trim($child->getAttribute('opcode'));
                
                if (!$order  !$opcode  !ctype_digit($order)  !filter_var($order, FILTER_VALIDATE_INT)  in_array((int) $order, $this->order_numbers) || (int) $order < 1) {
                    ErrorExit::exit_with_error(ReturnCode::INVALID_SOURCE_STRUCTURE, $this->stderr);
                }

                $this->order_numbers[] = (int) $order;
                $instruction = new Instruction($opcode, (int) $order);

                //get arguments of instruction
                foreach ($child->childNodes as $argNode) {
                    if ($argNode->nodeType === XML_ELEMENT_NODE) {
                        $this->getArgument($instruction, $argNode);
                    }
                }
                usort($instruction->args, fn($a, $b) => $a->arg_order <=> $b->arg_order);
                //check if instruction is valid
                if ($instruction->verifyInstructionValidity() === false) {
                    ErrorExit::exit_with_error(ReturnCode::INVALID_SOURCE_STRUCTURE, $this->stderr);
                }
                $this->instructions[] = $instruction;
            } else if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName !== 'instruction') {
                ErrorExit::exit_with_error(ReturnCode::INVALID_SOURCE_STRUCTURE, $this->stderr);
            }
        }
        usort($this->instructions, fn($a, $b) => $a->order <=> $b->order);