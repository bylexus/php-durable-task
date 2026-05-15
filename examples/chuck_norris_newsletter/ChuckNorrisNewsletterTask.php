<?php

namespace ByLexus\TaskRunner\Examples\chuck_norris_newsletter;

use ByLexus\TaskRunner\Attribute\CleanupAfter;
use ByLexus\TaskRunner\Examples\Support\SendMailStep;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use DateInterval;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

#[CleanupAfter(successful: new DateInterval('PT0H'), unsuccessful: new DateInterval('PT1H'))]
class ChuckNorrisNewsletterTask extends Task {
    public function __construct(protected PHPMailer $mailer, ?LoggerInterface $logger = null) {
        parent::__construct(logger: $logger);
    }

    public function nextStep(?Step $actStep = null): ?Step {
        if (!$actStep) {
            return new GetChuckNorrisJokeStep();
        }
        if ($actStep instanceof GetChuckNorrisJokeStep) {
            $joke = $this->getPayload(GetChuckNorrisJokeStep::class)->joke ?? '';
            $this->setSubject('Your daily Chuck Norris Joke');
            $this->setBody($joke);

            return new SendMailStep($this->mailer);
        }

        return null;
    }

    public function setTo(string $to): self {
        // Prepare payload for SendMailStep
        $this->getPayload(SendMailStep::class)->to = $to;
        return $this;
    }

    public function setFrom(string $from): self {
        // Prepare payload for SendMailStep
        $this->getPayload(SendMailStep::class)->from = $from;
        return $this;
    }

    public function setSubject(string $subject): self {
        // Prepare payload for SendMailStep
        $this->getPayload(SendMailStep::class)->subject = $subject;
        return $this;
    }

    public function setBody(string $body): self {
        // Prepare payload for SendMailStep
        $this->getPayload(SendMailStep::class)->body = $body;
        return $this;
    }
}
