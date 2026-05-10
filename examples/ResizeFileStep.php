<?php

use ByLexus\TaskRunner\Enum\StepStatus;
use ByLexus\TaskRunner\FileAttachment;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

class ResizeFileStep extends Step {
    public function execute(Task $task): StepResult {
        try {
            $file = $this->file($task);
            $width = $this->width($task);
            if (!$file) {
                throw new Exception('No file attached');
            }
            // do some not really resizing thingajinx .....
            // $file = FileAttachment::fromString("Haha, just a joke!", "joke.txt", 'text/plain');
            $this->getLogger()->debug("Resizing file " . $file->name() . "to {$width}");
            $imgdata = $file->contents();
            list($w, $h) = getimagesizefromstring($imgdata);
            $scale = $w / $width;
            $newW = $width;
            $newH = $h / $scale;
            $orig = imagecreatefromstring($imgdata);
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $orig, 0, 0, 0, 0, $newW, $newH, $w, $h);
            $tmpfile = tempnam('/tmp', static::class);
            imagepng($resized, $tmpfile);

            $this->setFile($task, FileAttachment::fromFile($tmpfile));
            $this->getLogger()->debug("Resizing done");

            return new StepResult(StepStatus::SUCCEEDED);
        } catch (Throwable $t) {
            return StepResult::failed(new ErrorInfo($t->getCode(), $t->getMessage()));
        }
    }

    public static function setFile(Task $task, FileAttachment $attachment) {
        $task->getPayload(static::class)->file = $attachment;
    }
    public static function file(Task $task): ?FileAttachment {
        return $task->getPayload(static::class)->file ?? null;
    }
    public static function setWidth(Task $task, int $width) {
        $task->getPayload(static::class)->width = $width ?? 500;
    }
    public static function width(Task $task): int {
        return $task->getPayload(static::class)->width ?? 500;
    }
}
