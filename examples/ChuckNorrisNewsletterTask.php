<?php

use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;

require_once(__DIR__ . '/GetChuckNorrisJokeStep.php');
require_once(__DIR__ . '/SendMailStep.php');

class ChuckNorrisNewsletterTask extends Task {
    public function nextStep(?Step $actStep = null): ?Step {
        if (!$actStep) {
            return new GetChuckNorrisJokeStep();
        }
        if (get_class($actStep) == GetChuckNorrisJokeStep::class) {
            return new SendMailStep();
        }

        return null;
    }
}
