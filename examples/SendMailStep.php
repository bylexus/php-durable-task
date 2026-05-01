<?php

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Result\ErrorInfo;
use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use PHPMailer\PHPMailer\PHPMailer;

class SendMailStep extends Step {
    public function execute(): StepResult {
        try {
            $payload = $this->getPayload();
            $mailer = new PHPMailer(true);
            $mailer->IsSMTP();
            $mailer->Host = 'localhost';
            $mailer->Port = '1025';
            $mailer->CharSet = "utf-8";
            $mailer->From = $payload->from ?? 'nobody@nobody.com';
            $mailer->addAddress($payload->to ?? '');
            $mailer->Subject = 'Your daily chuck norris joke!';
            $mailer->Body = $payload->joke->value ?? '-';
            $mailer->send();
            return new StepResult(StepStatus::SUCCEEDED, $payload);
        } catch (Throwable $t) {
            return new StepResult(StepStatus::FAILED, null, new ErrorInfo($t->getCode(), $t->getMessage()));
        }
    }
}
