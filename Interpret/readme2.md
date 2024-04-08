# Implementation Documentation for Task 2 of IPP 2023/2024

**Name and Surname:** Dmytro Trifonov 

**Login:** xtrifo00 

## Table of Contents

1. [Introduction](#introduction)
2. [Program Description](#program-description)
3. [Implementation Details](#implementation-details)
   - [Main Components](#main-components)
   - [Object-Oriented Programming](#object-oriented-programming)
        - [Encapsulation and Data Abstraction](#encapsulation-and-data-abstraction)
        - [Composition](#composition)
        - [Class Relationships and Responsibilities](#class-relationships-and-responsibilities)
        - [Practical Use of OOP for Flexibility and Maintenance](#practical-use-of-oop-for-flexibility-and-maintenance)
   - [Extension: STACK](#extension-stack)
4. [UML Diagram](#uml-diagram)
5. [Usage](#usage)
6. [Summary](#summary)

## Introduction

This document serves as a comprehensive guide on the implementation of the `interpret.php` script. It outlines the design philosophy, internal data representations, methodologies for solving specific problems including edge cases, and the implementation of extensions.

## Program Description
The interpret.php script is designed as an interpreter for    IPPcode24, a fictional programming language devised for the purposes of this project. The script takes XML-formatted input representing IPPcode23 programs and executes them, emulating the behavior of a hypothetical IPPcode23 machine. Here's an overview of the script's functionalities:

- **XML** Parsing: The script begins by parsing an XML file, which contains the IPPcode23 program. This XML file adheres to a specific schema that represents various program instructions and their associated arguments.

- **Instruction Interpretation:** After parsing, the script interprets each instruction sequentially. Instructions include operations such as arithmetic calculations, logic operations, variable manipulation, and program control flows (e.g., jumps, branches).

- **Variable and Frame Management:** The interpreter manages variables within three types of frames: Global Frame (GF), Local Frame (LF), and Temporary Frame (TF). Frame.php and FrameManager.php handle the scoping and lifetimes of these variables, supporting the script's ability to execute complex programs that require dynamic memory management.

- **Execution Control:** Control flow instructions alter the execution sequence based on conditions or explicitly specified jumps. This allows the execution of loops, conditional statements, and function calls, making the interpreter capable of running non-linear IPPcode23 programs.

- **Runtime Error Handling:** Throughout its execution, the script robustly handles runtime errors, such as undefined variables, type mismatches, and invalid instructions, ensuring that execution errors are reported clearly.

- **Output:** Depending on the program's logic and instructions, the script can modify data, perform computations, and produce output based on the interpreted IPPcode23 instructions.

## Implementation Details

### Main Components

- **Program.php**: Manages the parsing of the program and serves as the central point for executing instructions.
- **Instruction.php & InstructionDictionary.php**: Define the structure of instructions and map them to their respective implementations.
- **Argument.php**: Represents an instruction argument, encapsulating the logic for argument type checking and retrieval.
- **Frame.php & FrameManager.php**: Handle the storage, scope, and manipulation of variables within different frames (GF, LF, TF).
- **Stack.php**: Implements a stack structure for supporting various operations, including function calls and temporary value storage.
- **ErrorHandler.php**: Provides a mechanism for error handling and reporting throughout the script's execution.
- **XMLProcessing.php**: Facilitates the parsing of XML input, ensuring that the script can interpret instructions formatted in XML.

### Object-Oriented Programming
Our implementation of the interpret.php script applies several OOP principles directly reflecting the structure and functionality coded within our project. Here's how these principles manifest through our codebase's components:

#### Encapsulation and Data Abstraction
- **Frame and FrameManager Classes:** These classes encapsulate the management of variable scopes and lifecycles. For example, FrameManager acts as a central point for handling different frames (GF, LF, TF), showcasing how we abstract and encapsulate frame management logic.
- **Argument Class:** This class demonstrates encapsulation by managing argument-related data, including type and value, ensuring a clear separation between the argument data handling and instruction execution logic.
#### Composition
- **Program Class Composing Instruction Instances:** Our Program class maintains a list of Instruction objects, illustrating composition. This setup enables the Program class to manage the flow of instruction execution, where each Instruction is a part of the overall program structure but encapsulates its own execution logic.
#### Class Relationships and Responsibilities
- **Relationship Between ErrorHandler and Other Classes:** The ErrorHandler class is designed to encapsulate error handling logic, demonstrating a clear division of responsibility. It interacts with other components to gracefully manage and report errors, thus separating error handling from the main business logic.
#### Practical Use of OOP for Flexibility and Maintenance
The design choices made, reflected in the structured use of classes and their relationships, enhance the script's flexibility for future expansion. For instance, adding a new instruction type involves creating a new Instruction subclass, minimizing changes to the existing codebase.

### Extension: STACK

A notable enhancement in the `interpret.php` script is the addition of support for stacked instruction variants. This extension broadens the interpreter's functionality, enabling the processing of instructions that interact directly with a data stack, thereby facilitating a more dynamic and adaptable instruction set.

The stacked instruction variants supported include:

- **CLEARS**: Empties the data stack.
- **ADDS/SUBS/MULS/IDIVS**: Executes arithmetic operations (add, subtract, multiply, integer divide) using the top two elements on the data stack as operands.
- **LTS/GTS/EQS**: Conducts comparison operations (less than, greater than, equal to) between the top two elements on the data stack.
- **ANDS/ORS/NOTS**: Performs logical operations (AND, OR, NOT) on the top elements of the data stack.
- **INT2CHARS/STRI2INTS**: Transforms an integer to a character and a string to an integer, respectively, using the top element of the data stack.
- **JUMPIFEQS/JUMPIFNEQS**: Executes conditional jumps depending on the equality or inequality of the top two elements on the data stack.

These instructions adhere to the three-address instruction specification, selecting operands from the data stack in reverse order (usually first `⟨symb2⟩` and then `⟨symb1⟩`). This functionality enables succinct expression of complex operations and supports the creation of more intricate IPPcode23 programs through efficient use of the stack for temporary data storage and manipulation.

## UML Diagram

![Class Diagram](https://www.mermaidchart.com/raw/6e74bd31-dd4b-47f2-bc29-d6905da57868?theme=dark&version=v0.1&format=svg)

## Usage

Detailed instructions on how to run the `interpret.php` script, including necessary parameters and example command lines. Ensure to mention any prerequisites for running the script, such as PHP version requirements.

```bash
php interpret.php --source=file.xml [--input=file.in]
```
## Summary

The `interpret.php` script is a sophisticated interpreter designed to execute programs written in IPPcode23, a conceptual programming language created specifically for educational purposes. Through the adept application of Object-Oriented Programming (OOP) principles, the script provides a robust and flexible framework for parsing XML input, interpreting and executing instructions, managing variable scopes, and handling runtime errors efficiently. This implementation not only demonstrates the practical application of complex programming concepts but also serves as a foundational tool for understanding interpreter design and operation. With its structured approach to handling the intricacies of programming language interpretation, `interpret.php` stands as a testament to the power of OOP in building scalable, maintainable, and efficient software solutions.
