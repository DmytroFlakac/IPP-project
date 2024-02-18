import xml.etree.ElementTree as ET
import xml.dom.minidom

# Create the root element
program = ET.Element("program")
program.set("language", "IPPcode24")

# Create the instruction element
instruction = ET.SubElement(program, "instruction")
instruction.set("order", "1")
instruction.set("opcode", "WRITE")

# Create the argument element
arg1 = ET.SubElement(instruction, "arg1")
arg1.set("type", "bool")
arg1.text = "true"

# Generate XML string with declaration
xml_str = ET.tostring(program, encoding="utf-8")
parsed_xml = xml.dom.minidom.parseString(xml_str).toprettyxml(indent="\t")
print(parsed_xml)
 
