<?php

namespace ByLexus\TaskRunner\Examples\Support;

use ByLexus\TaskRunner\Queue\QueueConfiguration;
use ByLexus\TaskRunner\RunnerConfiguration;
use ByLexus\TaskRunner\Task;
use ByLexus\TaskRunner\TaskEnvironment;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ExampleServiceContainer implements ContainerInterface {
    private $services = [];
    public function __construct() {
        $this->services[PDO::class] = $this->createPDO();
        $this->services[PHPMailer::class] = $this->createMailer();
        $this->services[LoggerInterface::class] = new ConsoleLogger();
        $this->services[QueueConfiguration::class] = new QueueConfiguration();
        $this->services[RunnerConfiguration::class] = new RunnerConfiguration(bootstrapSchemaOnStart: true);
        $this->services[TaskEnvironment::class] = $this->createTaskEnvironment();
    }

    public function get(string $id) {
        // we create a new instance for each request - PHPMailer instances
        // are not shared:
        if ($id === PHPMailer::class) {
            return $this->createMailer();
        }
        return $this->services[$id] ?? null;
    }

    public function has(string $id): bool {
        return key_exists($id, $this->services);
    }

    private function createMailer(): PHPMailer {
        $mailer = new PHPMailer(true);
        $mailer->IsSMTP();
        $mailer->Host = 'localhost';
        $mailer->Port = '1025';
        $mailer->CharSet = "utf-8";
        return $mailer;
    }

    private function createPDO(): PDO {
        // Examples: The example connection uses these env vars:
        $dsn = getenv('EXAMPLE_DATABASE_DSN');
        $user = getenv('EXAMPLE_DATABASE_USER');
        $password = getenv('EXAMPLE_DATABASE_PASSWORD');
        return new PDO($dsn, $user, $password);
    }

    private function createTaskEnvironment() {
        $conn = $this->get(PDO::class);
        $qc = $this->get(QueueConfiguration::class);
        $rc = $this->get(RunnerConfiguration::class);
        $logger = $this->get(LoggerInterface::class);
        return new TaskEnvironment(
            connection: $conn,
            queueConfiguration: $qc,
            runnerConfiguration: $rc,
            container: $this,
            logger: $logger,
        );
    }
}
