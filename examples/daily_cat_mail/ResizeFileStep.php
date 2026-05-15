<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Examples\daily_cat_mail;

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
                throw new \Exception('No file attached');
            }
            $this->getLogger()->debug("Resizing file " . $file->name() . "to {$width}");
            $imgdata = $file->contents();
            list($w, $h) = getimagesizefromstring($imgdata);
            $scale = $w / $width;
            $newW = $width;
            $newH = $h / $scale;
            $orig = imagecreatefromstring($imgdata);
            $resized = imagecreatetruecolor((int)$newW, (int)$newH);
            imagecopyresampled($resized, $orig, 0, 0, 0, 0, (int)$newW, (int)$newH, (int)$w, (int)$h);
            $tmpfile = tempnam('/tmp', static::class);
            imagepng($resized, $tmpfile);

            $this->setFile($task, FileAttachment::fromFile($tmpfile, $file->name()));
            $this->getLogger()->debug("Resizing done");

            return new StepResult(StepStatus::SUCCEEDED);
        } catch (\Throwable $t) {
            return StepResult::failed(
                message: $t->getMessage(),
                errorInfo: new ErrorInfo($t->getCode(), $t->getMessage())
            );
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
