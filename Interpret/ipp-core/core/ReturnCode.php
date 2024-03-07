<?php
/**
 * IPP - PHP Project Core
 * @author Radim Kocman
 * @author Zbyněk Křivka
 * 
 * DO NOT MODIFY THIS FILE!
 */

namespace IPP\Core;

/**
 * Common script return codes from the project specification
 */
abstract class ReturnCode
{
    const OK = 0;
    const PARAMETER_ERROR = 10;
    const INPUT_FILE_ERROR = 11;
    const OUTPUT_FILE_ERROR = 12;
    const INVALID_XML_ERROR = 31;
    const INVALID_SOURCE_STRUCTURE = 32;
    const SEMANTIC_ERROR = 52;
    const OPERAND_TYPE_ERROR = 53;
    const VARIABLE_ACCESS_ERROR = 54;
    const FRAME_ACCESS_ERROR = 55;
    const VALUE_ERROR = 56;
    const OPERAND_VALUE_ERROR = 57;
    const STRING_OPERATION_ERROR = 58;
    const INTEGRATION_ERROR = 88;
    const INTERNAL_ERROR = 99;
}
