
# Implementation Documentation for Task 1 of IPP 2023/2024
**Name and Surname:** Dmytro Trifonov
**Login:** xtrifo00

## Introduction
This document serves as a comprehensive guide to the implementation of `parse.py`, a script developed for the first task of the IPP project 2023/2024. The script reads IPPcode24 source code, verifies its lexical and syntactic correctness, and outputs its XML representation.

## Usage
To use the script, run it from the command line with:
```bash
python3 parse.py < input_file > output_file.xml
```
The `--help` option provides usage information:
```bash
python3 parse.py --help
```

## Design Philosophy and Architecture
The script emphasizes modularity, robust error handling, and efficient parsing. Its architecture is detailed in the included diagram , showing the interaction between the InputProcessor, Parser, XML generator, and Argument classes.

## Class Diagram

![Class Diagram]([https://www.mermaidchart.com/raw/a3012c7c-990d-45fc-850c-e47d76e7742a?theme=dark&version=v0.1&format=svg](https://www.mermaidchart.com/raw/a3012c7c-990d-45fc-850c-e47d76e7742a?theme=dark&version=v0.1&format=svg))


### InputProcessor
Cleans input lines, ensuring correct formatting and header presence.

### Parser
Decomposes input into instructions and arguments, validates syntax, and maps to XML elements.

### XML
Generates the XML structure using Python's `xml.etree.ElementTree`, adhering to specified output format requirements.

### Argument Classes
Facilitates parsing and validation of instruction arguments based on type and value.

## Error Handling
Implements detailed error reporting for various failure scenarios, exiting with appropriate codes:
- 21: Incorrect or missing header.
- 22: Unknown or invalid opcode.
- 23: Other lexical or syntactic errors.

## Testing and Extensibility
The script's structure allows for straightforward unit testing and extensibility, accommodating future IPPcode24 modifications with minimal changes.

## Conclusion
`parse.py` is designed for clarity, maintainability, and adherence to IPPcode24 specifications, providing a robust solution for parsing and XML representation tasks.
