<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Examples\daily_cat_mail;

use ByLexus\TaskRunner\Enum\StepStatus;
use ByLexus\TaskRunner\FileAttachment;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

class GetDailyCatStep extends Step {
    protected const CAT_API = 'https://cataas.com/cat?width=500';

    public function execute(Task $task): StepResult {
        try {
            $this->getLogger()->debug("Fetching random cat from " . static::CAT_API);
            $file = FileAttachment::fromString(file_get_contents(static::CAT_API), 'daily-cat.png', 'image/png');
            $this->setCatFile($task, $file);
            $this->getLogger()->debug("Fetched random cat");
            return new StepResult(StepStatus::SUCCEEDED);
        } catch (\Throwable $t) {
            return StepResult::failed(
                message: $t->getMessage(),
                errorInfo: new ErrorInfo($t->getCode(), $t->getMessage())
            );
        }
    }

    protected static function setCatFile(Task $task, FileAttachment $attachment) {
        $task->getPayload(static::class)->file = $attachment;
    }
    public static function catFile(Task $task): ?FileAttachment {
        return $task->getPayload(static::class)->file;
    }
}
