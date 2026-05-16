<?php

declare(strict_types=1);

namespace ByLexus\TaskRunner\Tests\Fixture;

use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

final class SignalControlledShutdownStepFixture implements Step
{
    public function execute(Task $task): StepResult {
        $startedPath = getenv('PHP_TR_SIGNAL_STARTED_PATH');

        if (is_string($startedPath) && $startedPath !== '') {
            file_put_contents($startedPath, "started\n");
        }

        $releasePath = getenv('PHP_TR_SIGNAL_RELEASE_PATH');

        if (is_string($releasePath) && $releasePath !== '') {
            while (!is_file($releasePath)) {
                usleep(50_000);
            }
        } else {
            usleep(1_500_000);
        }

        return StepResult::succeeded(['completedAfterSignal' => true], 'Execution completed after shutdown request.');
    }
}
