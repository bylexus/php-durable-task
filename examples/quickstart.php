<?php

/**
 * Quickstart example - defines a Task (GreetingTask class)
 * and a single Step (PrintGreetingStep) to demonstrate a simple
 * example:
 *
 * - the Task defines the workflow: It defines methods to set/read the
 *   needed payload data, and defines the step workflow in the nextStep() method.
 *
 * - the Step reads the payload needed for its work from the Task and execute its
 *   behaviour. It then returns a step result when done.
 *
 * - below, the main code initiates a TaskEnvironment and a Runner
 *   to get started at the task.
 *
 * Execute with:
 *
 * php quickstart.php "Mona Lisa"
 */

declare(strict_types=1);

namespace ByLexus\TaskRunner\Examples\framework_integration;

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\TaskEnvironment;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;

require dirname(__DIR__) . '/vendor/autoload.php';

final class PrintGreetingStep implements Step {
    // Implement the execute function to execute the work:
    public function execute(Task $task): StepResult {
        // Steps read input from the task payload.
        $recipient = 'world';
        if ($task instanceof GreetingTask) {
            $recipient = $task->recipient();
        }

        // Do the work!
        fwrite(STDOUT, sprintf("Hello %s from a step.\n", $recipient));

        // and return a result:
        return StepResult::succeeded(message: 'Greeting printed.');
    }
}

#[CleanupAfter(new \DateInterval('PT10M'))]
final class GreetingTask extends Task {
    // Helper functions to get/set values from the Task's payload:
    public function setRecipient(string $recipient): self {
        $this->getPayload(static::class)->recipient = $recipient;
        return $this;
    }
    public function recipient(): string {
        return $this->getPayload(static::class)->recipient ?? 'world';
    }

    // nextStep allows the Task to form a workflow:
    // it receives the actual (done) step and can now return the next (configured) step.
    // Returning null means the flow is done:
    public function nextStep(?Step $actStep = null): ?Step {
        // Returning null ends the workflow. Returning a step queues the next unit of work.
        return $actStep === null ? new PrintGreetingStep() : null;
    }
}

// Keep connection settings overridable so the example can run unchanged in local setups.
// The default DSN targets PostgreSQL. quickstart only uses runSingle(), so no
// notification wakeup is involved; long-running runLoop() workers only get
// LISTEN / NOTIFY wakeups on PostgreSQL and poll on the other supported backends.
$dsn = getenv('EXAMPLE_DATABASE_DSN') ?: 'pgsql:host=127.0.0.1;port=5432;dbname=php_tr_test';
$user = getenv('EXAMPLE_DATABASE_USER') ?: 'postgres';
$password = getenv('EXAMPLE_DATABASE_PASSWORD') ?: 'postgres';

$pdo = new \PDO($dsn, $user, $password);

// Create a Task environment that keeps all the needed context (can be shared):
$env = new TaskEnvironment(connection: $pdo);

// Quickstart performs an explicit schema bootstrap instead of relying on worker startup side effects.
$env->getSchemaManager()->bootstrap();

// ---------------------------------  Task creation ------------------------
// The task owns the payload. Here we seed it before enqueueing the first step.
$task = (new GreetingTask())->setRecipient($argv[1] ?? 'PHP TR');

// enqueue the task!
$record = $env->enqueue($task);


// ---------------------------------  Runner ------------------------------
// A runner claims one queued row, hydrates the task and step, executes them, and persists the result.
$runner = $env->createRunner();

// runSingle() is the smallest useful worker mode for demos, tests, and cron-style processing.
$processed = $runner->runSingle();

fwrite(
    STDOUT,
    sprintf(
        "Processed %d task(s). Enqueued task id: %d\n",
        $processed,
        (int) $record->taskId,
    ),
);
