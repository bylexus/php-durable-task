<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Examples\long_running_with_cancel;

use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class ProcessLargeImportStep extends Step {
    public function execute(Task $task): StepResult {
        foreach ($this->chunkIds($task) as $chunkId) {
            $task->reload();

            if ($task->isCancelRequested()) {
                return StepResult::cancelled(
                    errorInfo: new ErrorInfo(499, $task->getCancelReason() ?? 'Cancellation requested.'),
                    meta: ['chunkId' => $chunkId],
                    message: $task->getCancelReason() ?? 'Cancellation requested.',
                );
            }

            $this->processChunk($task, $chunkId);
        }

        return StepResult::succeeded(message: 'Import completed.');
    }

    /**
     * @return iterable<int>
     */
    private function chunkIds(Task $task): iterable {
        if ($task instanceof ProcessLargeTask) {
            $amountOfWork = $task->getAmountOfWork();
            for ($i = 1; $i <= $amountOfWork; $i++) {
                yield $i;
            }
        } else {
            return;
        }
    }

    private function processChunk(Task $task, int $chunkId): void {
        $task->reload();
        $task->setPayload('actChunk', $chunkId)->persistPayload();

        sleep(1);
        $chunksDone = (array)$task->reload()->getPayload('chunksDone') ?: [];
        $chunksDone[] = $chunkId;
        $task->setPayload('chunksDone', $chunksDone)->persistPayload();
    }
}
