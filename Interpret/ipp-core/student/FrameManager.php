<?php

namespace IPP\Student;

class FrameManager
{
    public $globalFrame;
    private $localFrames;
    private $temporaryFrame;

    function __construct()
    {
        $this->globalFrame = new GF();
        $this->localFrames[] = null;
        $this->temporaryFrame = null;
    }

//    public function createLocalFrames(): void
//    {
//        $this->localFrames[] = new LF();
//    }

    public function createTemporaryFrame(): void
    {
        $this->temporaryFrame = new TF();
    }

    public function addVariable2Frame($frame, $name): void
    {
        if ($frame === "GF") {
            $this->globalFrame->addVariable($name);
        } else if ($frame === "LF") {
            if($this->localFrames === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            $this->localFrames[count($this->localFrames) - 1]->addVariable($name);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            $this->temporaryFrame->addVariable($name);
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid frame.");
        }
    }

    public function getFrameVariable($frame, $name)
    {
        if ($frame === "GF") {
            return $this->globalFrame->getVariable($name);
        } else if ($frame === "LF") {
            if($this->localFrames === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            return $this->localFrames[count($this->localFrames) - 1]->getVariable($name);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            return $this->temporaryFrame->getVariable($name);
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid frame.");
        }
    }

    public function setVariable2Frame($frame, $name, $value): void
    {
        if ($frame === "GF") {
            $this->globalFrame->setVariable($name, $value);
        } else if ($frame === "LF") {
            if($this->localFrames === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            $this->localFrames[count($this->localFrames) - 1]->setVariable($name, $value);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
            $this->temporaryFrame->setVariable($name, $value);
        } else {
            ErrorHandler::ErrorMessage(ErrorHandler::SEMANTIC_ERROR, "Invalid frame.");
        }
    }
    public function pushTemporaryFrame(): void
    {
        if($this->localFrames === null)
            $this->localFrames[] = new LF();
        if ($this->temporaryFrame !== null) {
            $this->localFrames[] = $this->temporaryFrame;
            $this->temporaryFrame = null;
        } else
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
    }

    public function popTemporaryFrame(): void
    {
        if ($this->localFrames !== null) {
            $this->temporaryFrame = array_pop($this->localFrames);
            if (empty($this->localFrames))
                $this->localFrames = null;
        } else
            ErrorHandler::ErrorMessage(ErrorHandler::RUNTIME_NONEXISTENT_FRAME, "Nedefinovaný rámec");
    }
}


class GF extends Frame
{
    public function __construct()
    {
        parent::__construct();
    }
}

class LF extends Frame
{
    public function __construct()
    {
        parent::__construct();
    }
}

class TF extends Frame
{
    public function __construct()
    {
        parent::__construct();
    }
}