<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\FileAttachment;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class AttachmentRoundtripStepFixture extends Step
{
    public function execute(Task $task): StepResult {
        $attachment = $task->getPayload()->attachment ?? null;

        if (!$attachment instanceof FileAttachment) {
            throw new \RuntimeException('Expected hydrated attachment in task payload.');
        }

        $targetPath = tempnam(sys_get_temp_dir(), 'durable-attachment-output-');

        if (!is_string($targetPath)) {
            throw new \RuntimeException('Failed to allocate attachment target file.');
        }

        try {
            $attachment->toFile($targetPath);
            $restoredContent = file_get_contents($targetPath);

            if ($restoredContent === false) {
                throw new \RuntimeException('Failed to read restored attachment target file.');
            }

            $task->getPayload()->attachmentRestoredContent = $restoredContent;

            return StepResult::succeeded(meta: ['attachmentRestored' => true]);
        } finally {
            @unlink($targetPath);
        }
    }
}
