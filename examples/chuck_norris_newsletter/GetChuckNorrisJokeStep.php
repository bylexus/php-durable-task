<?php

namespace ByLexus\TaskRunner\Examples\chuck_norris_newsletter;

use ByLexus\TaskRunner\Enum\StepStatus;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GetChuckNorrisJokeStep implements Step {
    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function execute(Task $task): StepResult {
        try {
            $this->logger->debug("Fetching that chuck joke.....");
            $json = file_get_contents('https://api.chucknorris.io/jokes/random');
            $joke = json_decode($json);
            $task->getPayload(static::class)->joke = $joke->value ?? '(oops)';
            sleep(rand(0, 4));
            $this->logger->debug("Fetched that chuck joke!");
            if (!empty($joke)) {
                return new StepResult(StepStatus::SUCCEEDED);
            }
            throw new \Error('Cannot read Chuck Norris Joke', 500);
        } catch (\Throwable $t) {
            return StepResult::failed(new ErrorInfo($t->getCode(), $t->getMessage()));
        }
    }
}
