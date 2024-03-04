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
    21 - wrong or missing header in the source code written in IPPcode23,
    22 - unknown or wrong opcode in the source code written in IPPcode23,
    23 - other lexical or syntactic error in the source code written in IPPcode23.""")

# Check for the --help argument
if len(sys.argv) > 1:
    if sys.argv[1] == "--help":
        print_help()
        sys.exit(0)
    elif len(sys.argv) > 1:
        input_data = sys.argv[1] #
    # else:
    #     sys.stderr.write("Error: Invalid argument!\n")
    #     sys.exit(10)
        
# input_data = sys.stdin.read()

if not input_data.strip():
    sys.stderr.write("Error: Empty input!\n")
    sys.exit(11)        

# Split the input into lines
lines = input_data.split("\n")

# Initialize an empty list to store cleaned lines
cleaned_lines = []

# Go through each line and clean up the input
for line in lines:
    # Remove multiple spaces
    line = re.sub(r'\s+', ' ', line)
    # Remove comments
    line = re.sub(r'#.*', '', line)
    # Remove leading and trailing spaces
    line = line.strip()
    # Add the cleaned line to the list if it's not empty
    if line:
        cleaned_lines.append(line)

# Reassign the cleaned lines to the original variable
lines = cleaned_lines


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
        self.var_regex = re.compile(r"GF@.*|LF@.*|TF@.*")
        self.symb_regex = re.compile(r"int@.*|bool@.*|string@.*|nil@.*")
        self.lable_regex = re.compile(r"[a-zA-Z_][a-zA-Z0-9_]*")
        
    def var_or_symb_type(self, arg):
        self.type = arg.split("@")[0]
        return self.type
    
    def var_or_symb_value(self, arg):
        self.value = arg.split("@")[1]
        return self.value
    
    def label(self, arg):
        self.type = "label"
        self.value = arg
        return self
        
        
  
class Parser:
    def __init__(self, lines):
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
        self.parse_lines(lines)

    def parse_lines(self, lines):
        for line in lines:
            parts = line.split()
            if parts:  # Check if the line is not empty after splitting
                opcode = parts[0]
                args = parts[1:]
                # print(str(args) + str(len(self.instructions) + 1))
                instruction = Instruction(opcode, len(self.instructions) + 1)  # Order starts from 1
                for arg in args:
                    instruction.add_arg(arg)
                # self.instructions.append(instruction)  
                self.parse(instruction)  
                 
    
    def parse(self, instruction: Instruction):
        opcode = instruction.opcode
        args = instruction.args
        in_dict = False
        for opcode_list in self.opcode_dict.values():
            if instruction.opcode in opcode_list:
                in_dict = True
                break
        for key, opcode_list in self.opcode_dict.items():
            if instruction.opcode in opcode_list:
                expected_args = key[0]
                print(expected_args)
                break
            
        if  in_dict:
            if len(args) != expected_args:
                sys.stderr.write(f"Error: Incorrect number of arguments for opcode {instruction.opcode}\n")
                sys.exit(22)
            
        else:
            sys.stderr.write(f"Error: Unknown opcode {instruction.opcode}\n")
            sys.exit(22)
                

    def header_xml(self):
        program_element = ET.Element("program")
        program_element.set("language", "IPPcode24")
        return program_element
    
    def add_instruction_xml(self, instruction, program_element):
        instruction_element = ET.SubElement(program_element, "instruction", order=str(instruction.order), opcode=instruction.opcode)
        for i, arg in enumerate(instruction.args, start=1):
            arg_obj = Argument(arg)
            arg_type = arg_obj.var_or_symb_type(arg)  # Pass the argument string to var_or_symb_type()
            arg_element = ET.SubElement(instruction_element, f"arg{i}", type=arg_type)
            arg_element.text = arg  # Pass the argument string to var_or_symb_value()
        return program_element

    
        # program_element = ET.Element("program")
        # program_element.set("language", "IPPcode24")
        # for instruction in self.instructions:
        #     instruction_element = ET.SubElement(program_element, "instruction", order=str(instruction.order), opcode=instruction.opcode)
        #     for arg in instruction.args:
        #         arg_element = ET.SubElement(instruction_element, "arg", type="var" if arg.value.startswith("GF@") else "label" if arg.value.isalpha() else "string")
        #         arg_element.text = arg.value
        # return program_element
        

    
if lines[0] != ".IPPcode24":
    sys.stderr.write("Error: Invalid header!\n")
    sys.exit(21)
    
lines = lines[1:]


# program = ET.Element("program")
# program.set("language", "IPPcode24")



# for line in lines:
#     print(line)
# xml_str = ET.tostring(program, encoding="utf-8")
# parsed_xml = xml.dom.minidom.parseString(xml_str).toprettyxml(indent="\t")
# print(parsed_xml)
# parser = Parser(lines)
# xml_tree = parser.to_xml()
# xml_str = ET.tostring(xml_tree, encoding="utf-8")
# parsed_xml = xml.dom.minidom.parseString(xml_str).toprettyxml(indent="\t")
# print(parsed_xml)
parser = Parser(lines)

# Generate the XML element for the program header
program_element = parser.header_xml()

# Add instructions to the program element
for instruction in parser.instructions:
    parser.add_instruction_xml(instruction, program_element)

# Create the XML tree
xml_tree = ET.ElementTree(program_element)

# Output the XML
xml_tree.write("program.xml", encoding="UTF-8", xml_declaration=True)



 # def add_var(self, var):
    #     self.type = var.split("@")[0]    
    #     self.value = var.split("@")[1]
    #     return self
        
# def to_xml(self):
    #     instruction_element = ET.Element("instruction", order=str(self.order), opcode=self.opcode)
    #     for arg in self.args:
    #         arg_element = ET.SubElement(instruction_element, "arg1", type="var" if arg.startswith("GF@") else "label" if arg.isalpha() else "string")
    #         arg_element.text = arg
    #     return instruction_element