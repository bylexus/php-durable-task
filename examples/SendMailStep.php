<?php

use ByLexus\DurableTask\Enum\StepStatus;
use ByLexus\DurableTask\Result\ErrorInfo;
use ByLexus\DurableTask\Result\StepResult;
use ByLexus\DurableTask\Step;
use ByLexus\DurableTask\Task;
use PHPMailer\PHPMailer\PHPMailer;

class SendMailStep extends Step {
    public function execute(Task $task): StepResult {
        try {
            $payload = $task->getPayload(static::class);
            $mailer = new PHPMailer(true);
            $mailer->IsSMTP();
            $mailer->Host = 'localhost';
            $mailer->Port = '1025';
            $mailer->CharSet = "utf-8";
            $mailer->From = $payload->from ?? 'nobody@nobody.com';
            $mailer->addAddress($payload->to ?? '');
            $mailer->Subject = $payload->subject ?? '';
            $mailer->Body = $payload->body ?? '-';
            $mailer->send();
            return new StepResult(StepStatus::SUCCEEDED);
        } catch (Throwable $t) {
            return StepResult::failed(new ErrorInfo($t->getCode(), $t->getMessage()));
        }
    }
}
