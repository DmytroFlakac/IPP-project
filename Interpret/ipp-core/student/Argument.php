<?php

namespace IPP\Student;


class Argument
{
    public string|null $type;
    public string|null $frame;
    public string|null $name;
    public string|null|bool|int $value;

    public function __construct()
    {
        $this->type = null;
        $this->frame = null;
        $this->name = null;
        $this->value = null;
    }

    /**
     * @param string $str
     * @return string
     */
    public function decodeStringArgument($str): string
    {
        // First, replace common escape sequences
        $replacements = [
            '\\n' => "\n", // New line
            '\\r' => "\r", // Carriage return
            '\\t' => "\t", // Tab
            '\\\\' => "\\", // Backslash
            // Add more common escape sequences as needed
        ];

        foreach ($replacements as $search => $replace) {
            $str = str_replace($search, $replace, $str);
        }

        return preg_replace_callback('/\\\\([0-9]{3})/', function($matches) {
            return chr((int)$matches[1]);
        }, $str);
    }

    /**
     * @param Argument $arg
     * @param FrameManager $frameManager
     * @return mixed
     */
    public static function getArgData($arg, $frameManager) {
        if ($arg->type === "var") {
            $var = $frameManager->getFrameVariable($arg->frame, $arg->name);
        } else {
            $var = $arg;
        }
        return $var;
    }

    /**
     * @param string $opcode
     * @return bool
     */
    public static function isCALLorJUMP($opcode)
    {
        return $opcode === "CALL" || str_contains($opcode, "JUMP");
    }
}