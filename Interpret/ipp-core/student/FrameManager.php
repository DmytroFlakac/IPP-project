<?php

namespace IPP\Student;

class FrameManager
{
    private $globalFrame;
    private $localFrames = null;
    private $temporaryFrame = null;

    function __construct()
    {
        $this->globalFrame = new GF();
    }

    public function createLocalFrames(): void
    {
        $this->localFrames[] = new LF();
    }

    public function createTemporaryFrame(): void
    {
        $this->temporaryFrame = new TF();
    }

    public function pushTemporaryFrame(): void
    {
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