<?php

namespace ByLexus\TaskRunner\Examples\Support;

use ByLexus\TaskRunner\Enum\StepStatus;
use ByLexus\TaskRunner\FileAttachment;
use ByLexus\TaskRunner\Result\ErrorInfo;
use ByLexus\TaskRunner\Result\StepResult;
use ByLexus\TaskRunner\Step;
use ByLexus\TaskRunner\Task;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

class SendMailStep extends Step {
    public function __construct(protected PHPMailer $mailer, ?LoggerInterface $logger = null) {
        parent::__construct(logger: $logger);
    }

    public function execute(Task $task): StepResult {
        try {
            $this->getLogger()->debug("Sending an email ...");
            $payload = $task->getPayload(static::class);
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->From = $payload->from ?? 'nobody@nobody.com';
            $this->mailer->addAddress($payload->to ?? '');
            $this->mailer->Subject = $payload->subject ?? '';
            $this->mailer->Body = $payload->body ?? '-';
            $tmpFiles = [];
            if (!empty($payload->attachments)) {
                foreach ($payload->attachments as $attachment) {
                    /** @var FileAttachment */
                    $a = $attachment;
                    $path = tempnam('/tmp', static::class);

                    $tmpFiles[] = $path;
                    $a->toFile($path);
                    $this->getLogger()->debug("File is present: " . is_file($path));
                    $this->getLogger()->debug("File size: " . filesize($path));
                    $this->mailer->addAttachment($path, name: basename($a->name()), type:$a->mimeType());
                }
            }
            $this->mailer->send();
            foreach ($tmpFiles as $path) {
                @unlink($path);
            }
            $this->getLogger()->debug("Sending an email - DONE!");
            return new StepResult(StepStatus::SUCCEEDED);
        } catch (\Throwable $t) {
            return StepResult::failed(
                message: $t->getMessage(),
                errorInfo: new ErrorInfo($t->getCode(), $t->getMessage())
            );
        }
    }

    public static function setTo(Task $task, string $to) {
        $task->getPayload(static::class)->to = $to;
    }

    public static function setFrom(Task $task, string $from) {
        $task->getPayload(static::class)->from = $from;
    }

    public static function setSubject(Task $task, string $subject) {
        $task->getPayload(static::class)->subject = $subject;
    }

    public static function setBody(Task $task, string $body) {
        $task->getPayload(static::class)->body = $body;
    }

    public static function setAttachments(Task $task, array $attachments) {
        $task->getPayload(static::class)->attachments = [];
        $task->getPayload(static::class)->attachments = $attachments;
    }

    public static function addAttachment(Task $task, FileAttachment $attachment) {
        if (empty($task->getPayload(static::class)->attachments)) {
            $task->getPayload(static::class)->attachments = [];
        }
        $task->getPayload(static::class)->attachments[] = $attachment;
    }
}
