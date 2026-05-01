<?php

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Result\ErrorInfo;
use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;

class GetChuckNorrisJokeStep extends Step {
    public function execute(): StepResult {
        try {
            $payload = $this->getPayload();
            $json = file_get_contents('https://api.chucknorris.io/jokes/random');
            $joke = json_decode($json);
            $payload->joke = $joke;
            if (!empty($joke)) {
                return new StepResult(StepStatus::SUCCEEDED, $payload);
            }
            throw new Error('Cannot read Chuck Norris Joke', 500);
        } catch (Throwable $t) {
            return new StepResult(StepStatus::FAILED, null, new ErrorInfo($t->getCode(), $t->getMessage()));
        }
    }
}
