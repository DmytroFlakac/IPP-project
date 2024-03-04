import sys
import re
import xml.etree.ElementTree as ET
import xml.dom.minidom
from typing import List

# Function to print the help message
def print_help():
    print("""parse.py (IPP project 2024 - part 1)
Script of type filter reads the source code in IPPcode24 from the standard input,
checks the lexical and syntactic correctness of the code and prints the XML representation
of the program on the standard output.
Usage:
    python3 parse.py [--help]
Options:
    --help - prints this help message
Error codes:
    21 - wrong or missing header in the source code written in IPPcode24,
    22 - unknown or wrong opcode in the source code written in IPPcode24,
    23 - other lexical or syntactic error in the source code written in IPPcode24.""")

def input_processing(input_data):
    if not input_data.strip():
        sys.stderr.write("Error: Empty input!\n")
        sys.exit(21)     
    lines = input_data.split("\n")
    cleaned_lines = []
    for line in lines:
        line = re.sub(r'\s+', ' ', line)
        line = re.sub(r'#.*', '', line)
        line = line.strip()
        if line:
            cleaned_lines.append(line)
    lines = cleaned_lines
    if lines[0] != ".IPPcode24":
        sys.stderr.write("Error: Invalid header!\n")
        sys.exit(21)   
    lines = lines[1:] 
    return lines 
class Instruction:
    def __init__(self, opcode, order):
        self.opcode = opcode
        self.order = order
        self.args: List[Argument] = []

    def add_arg(self, arg):
        self.args.append(arg)

class Argument:
    def __init__(self, arg):
        self.arg = arg
        self.int_pattern = r'[+-]?\b(0[xX][0-9a-fA-F]+|0[oO][0-7]+|[0-9]+)\b'
        self.bool_pattern = r'\b(true|false)\b'
        self.string_pattern = r'^((\\[0-2][0-9]{2})|[^\\\s#])*?$'
        self.nil_pattern = r'\bnil\b'
        self.label_pattern = r'^[A-Za-z_\-&%!*?$][A-Za-z0-9_\-&%!*?$]*$'
        self.type_pattern = r'^(int|bool|string|nil)$'
        self.var_pattern = r'^(LF|TF|GF)@([A-Za-z_\-&%!*?$][A-Za-z0-9_\-&%!*?$]*)$'   
    def check_arg(self, arg_type, arg):       
        if arg_type == "literal":
            arg_type = self.get_type(arg)
            arg_value = self.get_value(arg)
            if arg_type == "int":
                self.check_by_regex(arg_value, self.int_pattern)
            elif arg_type == "bool":
                self.check_by_regex(arg_value, self.bool_pattern)
            elif arg_type == "string":           
                if not arg_value == "":
                    self.check_by_regex(arg_value, self.string_pattern)                   
            elif arg_type == "nil":
                self.check_by_regex(arg_value, self.nil_pattern)
        elif arg_type == "label":
            self.check_by_regex(arg, self.label_pattern)
        elif arg_type == "type":
            self.check_by_regex(arg, self.type_pattern)
        elif arg_type == "var":
            self.check_by_regex(arg, self.var_pattern)       
        else:
            sys.stderr.write(f"Error: Unknown argument type or value '{arg_type}' '{arg_value}'\n")
            sys.exit(23)
                 
    def check_by_regex(self, arg, regex):
        if not re.match(regex, arg):
            sys.stderr.write(f"Error: Invalid argument '{arg}'\n")
            sys.exit(23)      
    def get_value(self, arg):
        self.value = arg.partition("@")[2]
        return self.value
    
    def get_type(self, arg):
        self.type = arg.split("@")[0]
        if(self.type == "GF" or self.type == "LF" or self.type == "TF"):
            return "var"
        if(self.type == "int" or self.type == "bool" or self.type == "string" or self.type == "nil"):
            return self.type
        sys.stderr.write(f"Error: Unknown argument type '{self.type}'\n")
        sys.exit(23)    
  
class Parser:
    def __init__(self):
        self.instructions = []
        self.opcode_dict = {
            # Instructions with 0 arguments
            (0,): ["CREATEFRAME", "PUSHFRAME", "POPFRAME", "RETURN", "BREAK"],            
            # Instructions with 1 argument
            (1, "var"): ["DEFVAR", "POPS"],
            (1, "label"): ["CALL", "LABEL", "JUMP"],
            (1, "symb"): ["PUSHS", "WRITE", "EXIT", "DPRINT"],            
            # Instructions with 2 arguments
            (2, "var", "symb"): ["MOVE", "NOT", "INT2CHAR", "STRLEN", "TYPE"],
            (2, "var", "type"): ["READ"],           
            # Instructions with 3 arguments
            (3, "var", "symb", "symb"): [
                "ADD", "SUB", "MUL", "IDIV", "LT", "GT", "EQ", "AND", "OR",
                "STRI2INT", "CONCAT", "GETCHAR", "SETCHAR"
            ],
            (3, "label", "symb", "symb"): ["JUMPIFEQ", "JUMPIFNEQ"]
        }

    def parse(self, lines, program_element):
        for line in lines:
            parts = line.split()
            if parts:  # Check if the line is not empty after splitting
                if parts[0] == ".IPPcode24":
                    sys.stderr.write("Error: Double header!\n")
                    sys.exit(23)
                opcode = parts[0].upper()
                args = parts[1:]
                instruction = Instruction(opcode, len(self.instructions) + 1)  # Order starts from 1
                for arg in args:
                    instruction.add_arg(arg)
                self.instructions.append(instruction)
                self.parse_instruction(instruction, program_element)      
                 
    
    def parse_instruction(self, instruction: Instruction, program_element):
        keys_associated = []
        args = instruction.args
        in_dict = False
        for key, opcode_list in self.opcode_dict.items():
            if instruction.opcode in opcode_list:
                in_dict = True
                expected_args = key[0]
                keys_associated = key[1:]
                break
                   
        if  in_dict:
            if len(args) != expected_args:
                sys.stderr.write(f"Error: Incorrect number of arguments for opcode {instruction.opcode}\n")
                sys.exit(23) 
            self.add_instruction_xml(instruction, program_element, keys_associated)         
        else:
            sys.stderr.write(f"Error: Unknown opcode {instruction.opcode}\n")
            sys.exit(22)
        return keys_associated       

    def header_xml(self):
        program_element = ET.Element("program")
        program_element.set("language", "IPPcode24")
        return program_element
    
    def add_instruction_xml(self, instruction, program_element, arguments):
        instruction_element = ET.SubElement(program_element, "instruction", order=str(instruction.order), opcode=instruction.opcode)
        
        for i, arg in enumerate(instruction.args, start=1):
            arg_obj = Argument(arg)
            expected_arg_type = arg_type = arguments[i - 1]
            arg_text = arg
            if expected_arg_type == "var":
                arg_obj.check_arg("var", arg)               
            elif expected_arg_type == "symb":
                if arg_obj.get_type(arg) == "var":
                    arg_obj.check_arg("var", arg)   
                    arg_type = "var"    
                else:
                    arg_obj.check_arg("literal", arg)
                    arg_type = arg_obj.get_type(arg)
                    arg_text = arg_obj.get_value(arg)
            elif expected_arg_type == "label":
                arg_obj.check_arg("label", arg)
            elif expected_arg_type == "type":
                arg_obj.check_arg("type", arg)
            
            arg_element = ET.SubElement(instruction_element, f"arg{i}", type=arg_type)
            arg_element.text = arg_text    
        return program_element

def main():
    input_data = sys.argv[1]
    try:
        with open(input_data, 'r', encoding='utf-8') as file:
            input_data = file.read()
    except FileNotFoundError:
        print(f"Error: File '{input_data}' not found.")
        sys.exit(11)
    # input_data = sys.stdin.read()
    lines = input_processing(input_data)       
    parser = Parser()
    program_element = parser.header_xml()
    parser.parse(lines, program_element)
    xml_str = ET.tostring(program_element, encoding="UTF-8")
    xml_str = xml.dom.minidom.parseString(xml_str).toprettyxml(indent="    ", encoding="UTF-8").decode()

    sys.stdout.write(xml_str)

if __name__ == "__main__":
    
    main()
