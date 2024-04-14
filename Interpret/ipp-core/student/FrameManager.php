<?php

namespace IPP\Student;

use IPP\Core\ReturnCode;

class FrameManager
{
    public Frame $globalFrame;

    /** @var array<Frame> */
    public $localFrames;
    public Frame|null $temporaryFrame;

    function __construct()
    {
        $this->globalFrame = new Frame();
        $this->localFrames = [];
        $this->temporaryFrame = null;
    }

    /**
     * @return void
     */
    public function createTemporaryFrame()
    {
        $this->temporaryFrame = new Frame();
    }

    /**
     * @param string $frame
     * @param string $name
     * @return void
     */
    public function addVariable2Frame($frame, $name)
    {
        if ($frame === "GF") {
            $this->globalFrame->addVariable($name);
        } else if ($frame === "LF") {
            if(empty($this->localFrames))
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Local frame is not defined.", -1);
            $this->localFrames[count($this->localFrames) - 1]->addVariable($name);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is not defined.", -1);
            $this->temporaryFrame->addVariable($name);
        } 
    }

    /**
     * @param string $frame
     * @param string $name
     * @return mixed
     */
    public function getFrameVariable($frame, $name)
    {
        if ($frame === "GF") {
            return $this->globalFrame->getVariable($name);
        } else if ($frame === "LF") {
            if(empty($this->localFrames))
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Local frame is not defined.", -1);
            return $this->localFrames[count($this->localFrames) - 1]->getVariable($name);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is not defined.", -1);
            return $this->temporaryFrame->getVariable($name);
        } 
    }

    /**
     * @param string $frame
     * @param string $name
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public function setVariable2Frame($frame, $name, $value, $type): void
    {
        if ($frame === "GF") {
            $this->globalFrame->setVariable($name, $value, $type);
        } else if ($frame === "LF") {
            if(empty($this->localFrames))
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Local frame is not defined.", -1);
            $this->localFrames[count($this->localFrames) - 1]->setVariable($name, $value, $type);
        } else if ($frame === "TF") {
            if($this->temporaryFrame === null)
                ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "Temporary frame is not defined.", -1);
            $this->temporaryFrame->setVariable($name, $value, $type);
        } 
    }
    /**
     * @return void
     */
    public function pushTemporaryFrame()
    {
        if(empty($this->localFrames))
            $this->localFrames[] = new Frame();
        if ($this->temporaryFrame !== null) {
            $this->localFrames[] = $this->temporaryFrame;
            $this->temporaryFrame = null;
        } else
            ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "undefined frame", -1);
    }

    /**
     * @return void
     */
    public function popTemporaryFrame()
    {
        if (!empty($this->localFrames)) {
            $this->temporaryFrame = array_pop($this->localFrames);
            if (empty($this->localFrames))
                $this->localFrames = [];
        }
        else
            ErrorHandler::ErrorMessage(ReturnCode::FRAME_ACCESS_ERROR, "undefined frame", -1);
    }
}