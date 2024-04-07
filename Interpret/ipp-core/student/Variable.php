<?php

namespace IPP\Student;

class Variable
{
    public string $type;
    public int|string|bool|null $value;

    function __construct()
    {
        $this->type = "";
        $this->value = null;
    }
}