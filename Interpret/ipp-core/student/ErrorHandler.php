<?php

namespace IPP\Student;
use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;
use Throwable;

class ErrorHandler
{
    /**
     * Generate and throw an exception with a detailed message and associated error code.
     * 
     * @param int $code The error code associated with this error.
     * @param string $message The error message to be conveyed.
     * @param int $order The order number of the instruction that caused the error (if applicable).
     * @throws ErrorCodeException
     */
    public static function ErrorMessage(int $code, string $message, int $order = -1): void
    {
        $errorName = self::getErrorName($code);
        $fullMessage = $order === -1 ? "$errorName: $message" : "$errorName: $message: Instruction-> $order";
        
        // Default values for the additional parameters
        $previous = null; // Default previous exception
        $showTrace = true; // Default show trace

        if ($code === ReturnCode::OK) {
            $showTrace = false;
        }
        

        throw new ErrorCodeException($fullMessage, $code, $previous, $showTrace);
    }

    /**
     * Returns the name of the error based on the error code.
     * 
     * @param int $code The error code.
     * @return string The name or description of the error.
     */
    private static function getErrorName(int $code): string
    {
        switch ($code) {
            case ReturnCode::OK:
                return "No Error";
            case ReturnCode::PARAMETER_ERROR:
                return "Parameter Error";
            case ReturnCode::INPUT_FILE_ERROR:
                return "Input File Error";
            case ReturnCode::OUTPUT_FILE_ERROR:
                return "Output File Error";
            case ReturnCode::INVALID_XML_ERROR:
                return "Invalid XML Format";
            case ReturnCode::INVALID_SOURCE_STRUCTURE:
                return "Invalid Source Structure";
            case ReturnCode::SEMANTIC_ERROR:
                return "Semantic Error";
            case ReturnCode::OPERAND_TYPE_ERROR:
                return "Operand Type Error";
            case ReturnCode::VARIABLE_ACCESS_ERROR:
                return "Variable Access Error";
            case ReturnCode::FRAME_ACCESS_ERROR:
                return "Frame Access Error";
            case ReturnCode::VALUE_ERROR:
                return "Value Error";
            case ReturnCode::OPERAND_VALUE_ERROR:
                return "Operand Value Error";
            case ReturnCode::STRING_OPERATION_ERROR:
                return "String Operation Error";
            case ReturnCode::INTEGRATION_ERROR:
                return "Integration Error";
            case ReturnCode::INTERNAL_ERROR:
                return "Internal Error";
            default:
                return "Unknown Error";
        }
    }
}


/**
 * Specific exception class for handling error codes and messages within the IPP project.
 */
class ErrorCodeException extends IPPException
{
    public function __construct(string $message, int $code, ?Throwable $previous = null, bool $showTrace = true)
    {
        parent::__construct($message, $code, $previous, $showTrace);
    }
}
