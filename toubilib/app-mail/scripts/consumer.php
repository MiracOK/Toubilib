<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Toubilib\Scripts\SymfonyMailerAdapter;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
$user = getenv('RABBITMQ_USER') ?: 'toubi';
$pass = getenv('RABBITMQ_PASS') ?: 'toubi';

$mailHost = getenv('MAILER_HOST') ?: 'mail.toubi';
$mailPort = getenv('MAILER_PORT') ?: 1025;
$mailerDsn = getenv('MAILER_DSN') ?: "smtp://{$mailHost}:{$mailPort}";

$exchange = 'toubilib.events';
$exchangeType = 'topic';
$mailQueue = 'toubilib.mail.queue';
$bindingKey = 'rdv.*.*.email';

echo "[mail.sender] connecting to {$host}:{$port}\n";

try {
    $conn = new AMQPStreamConnection($host, $port, $user, $pass);
    $ch = $conn->channel();

    $ch->exchange_declare($exchange, $exchangeType, false, true, false);
    $ch->queue_declare($mailQueue, false, true, false, false);
    $ch->queue_bind($mailQueue, $exchange, $bindingKey);

    echo "[mail.sender] waiting for messages on queue {$mailQueue} (binding {$bindingKey})\n";

    $mailer = new SymfonyMailerAdapter($mailerDsn);

    $formatReadable = function (array $decoded): string {
        $lines = [];
        $lines[] = "Event: " . ($decoded['event_type'] ?? 'n/a');
        $lines[] = "Event id: " . ($decoded['event_id'] ?? 'n/a');
        $lines[] = "Occurred at: " . ($decoded['occurred_at'] ?? 'n/a');
        $lines[] = "";
        $payload = $decoded['payload'] ?? [];
        $lines[] = "Rendez-vous:";
        $lines[] = "  id: " . ($payload['id'] ?? $payload['rdvId'] ?? 'n/a');
        if (!empty($payload['date_heure_debut'])) $lines[] = "  début: " . $payload['date_heure_debut'];
        if (!empty($payload['date_heure_fin'])) $lines[] = "  fin: " . $payload['date_heure_fin'];
        if (!empty($payload['start'])) $lines[] = "  début: " . $payload['start'];
        if (!empty($payload['end'])) $lines[] = "  fin: " . $payload['end'];
        $patient = $payload['patient'] ?? [];
        if (!empty($patient)) {
            $lines[] = "";
            $lines[] = "Patient:";
            $lines[] = "  id: " . ($patient['id'] ?? 'n/a');
            $lines[] = "  email: " . ($patient['email'] ?? 'n/a');
            $lines[] = "  phone: " . ($patient['phone'] ?? 'n/a');
        }
        $prat = $payload['praticien'] ?? [];
        if (!empty($prat)) {
            $lines[] = "";
            $lines[] = "Praticien:";
            $lines[] = "  id: " . ($prat['id'] ?? 'n/a');
            $lines[] = "  email: " . ($prat['email'] ?? 'n/a');
            $lines[] = "  phone: " . ($prat['phone'] ?? 'n/a');
        }
        return implode("\n", $lines);
    };

    $callback = function (AMQPMessage $msg) use ($mailer, $formatReadable) {
        $body = (string)$msg->getBody();
        $decoded = json_decode($body, true);

        echo "----- New message -----\n";
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo $body . "\n";
            $msg->ack();
            return;
        }

        $eventType = $decoded['event_type'] ?? 'rdv.unknown';
        $payload = $decoded['payload'] ?? [];
        $recipient = $decoded['recipient'] ?? [];

        $recipientType = $recipient['type'] ?? null;

        if ($recipientType === 'patient') {
            $recipientEmail = $recipient['email'] ?? ($payload['patient_email'] ?? ($payload['patient']['email'] ?? null));
        } elseif ($recipientType === 'praticien') {
            $recipientEmail = $recipient['email'] ?? ($payload['praticien_email'] ?? ($payload['praticien']['email'] ?? null));
        } else {
            $recipientEmail = $recipient['email'] ?? ($payload['patient_email'] ?? ($payload['praticien_email'] ?? ($payload['patient']['email'] ?? ($payload['praticien']['email'] ?? null))));
        }


        if (empty($recipientEmail)) {
            echo "[mail.sender][WARN] no recipient email for type {$recipientType}, skipping send\n";
            $msg->ack();
            echo "----- End message -----\n";
            return;
        }

        $fromExplicit = $decoded['from'] ?? null;
        if (!empty($fromExplicit)) {
            $from = $fromExplicit;
        } else {
            if ($recipientType === 'patient') {
                $from = $payload['praticien_email'] ?? ($payload['praticien']['email'] ?? 'no-reply@toubilib.local');
            } elseif ($recipientType === 'praticien') {
                $from = $payload['patient_email'] ?? ($payload['patient']['email'] ?? 'no-reply@toubilib.local');
            } else {
                $from = $payload['praticien_email'] ?? ($payload['praticien']['email'] ?? ($payload['patient_email'] ?? ($payload['patient']['email'] ?? 'no-reply@toubilib.local')));
            }
        }


        $subject = "Notification " . $eventType;
        $text = $formatReadable($decoded);
        $html = "<pre>" . htmlentities(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";

        try {
            $mailer->send($recipientEmail, $subject, $html, $text, $from);
            echo "[mail.sender] mail sent to {$recipientEmail} (from {$from})\n";
        } catch (\Throwable $e) {
            echo "[mail.sender][ERROR] mail send failed: " . $e->getMessage() . "\n";
        }

        $msg->ack();
    };

    $ch->basic_qos(null, 1, null);
    $ch->basic_consume($mailQueue, '', false, false, false, false, $callback);

    while ($ch->is_open()) {
        try {
            $ch->wait();
        } catch (\Exception $e) {
            echo "[mail.sender][ERROR] " . $e->getMessage() . "\n";
            break;
        }
    }

    $ch->close();
    $conn->close();
    echo "[mail.sender] stopped\n";
} catch (\Throwable $e) {
    echo "[mail.sender][FATAL] " . $e->getMessage() . "\n";
    exit(1);
}