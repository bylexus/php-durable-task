<?php

use ByLexus\DurableTask\Attribute\CleanupAfter;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

require_once(__DIR__ . '/GetDailyCatStep.php');
require_once(__DIR__ . '/SendMailStep.php');
require_once(__DIR__ . '/ResizeFileStep.php');

#[CleanupAfter(successful: new DateInterval('PT0H'), unsuccessful: new DateInterval('PT1H'))]
class DailyCatTask extends Task {
    public function __construct(protected PHPMailer $mailer, ?LoggerInterface $logger = null) {
        parent::__construct(logger: $logger);
    }

    public function nextStep(?Step $actStep = null): ?Step {
        if (!$actStep) {
            return new GetDailyCatStep();
        }
        if ($actStep instanceof GetDailyCatStep) {
            $s = new ResizeFileStep(logger: $this->getLogger());
            $s->setWidth($this, 1000);
            $s->setFile($this, $actStep->catFile($this));
            return $s;
        }
        if ($actStep instanceof ResizeFileStep) {
            $s = new SendMailStep($this->mailer);
            $s->setSubject($this, 'Your daily Cat!');
            $s->setBody($this, 'Please find attached your daily cat.');
            $s->addAttachment($this, $actStep->file($this));
            return $s;
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
}
