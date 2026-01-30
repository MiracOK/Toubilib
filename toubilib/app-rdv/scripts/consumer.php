<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = (int)(getenv('RABBITMQ_PORT') ?: 5672);
$user = getenv('RABBITMQ_USER') ?: 'toubi';
$pass = getenv('RABBITMQ_PASS') ?: 'toubi';

$exchange = 'toubilib.events';
$exchangeType = 'topic';
$mailQueue = 'toubilib.mail.queue';
$bindingKey = 'rdv.*.*.email';

echo "[consumer] connecting to {$host}:{$port}\n";

try {
    $conn = new AMQPStreamConnection($host, $port, $user, $pass);
    $ch = $conn->channel();

    $ch->exchange_declare($exchange, $exchangeType, false, true, false);
    $ch->queue_declare($mailQueue, false, true, false, false);
    $ch->queue_bind($mailQueue, $exchange, $bindingKey);

    echo "[consumer] waiting for messages on queue {$mailQueue} (binding {$bindingKey})\n";

    $callback = function (AMQPMessage $msg) {
        $body = (string)$msg->getBody();
        $decoded = json_decode($body, true);
        echo "----- New message -----\n";
        if (json_last_error() === JSON_ERROR_NONE) {
            echo print_r($decoded, true);
        } else {
            echo $body . "\n";
        }
        echo "----- End message -----\n";
        $msg->ack();
    };

    $ch->basic_qos(null, 1, null);
    $ch->basic_consume($mailQueue, '', false, false, false, false, $callback);

    while ($ch->is_open()) {
        try {
            $ch->wait();
        } catch (\Exception $e) {
            echo "[consumer][ERROR] " . $e->getMessage() . "\n";
            break;
        }
    }

    $ch->close();
    $conn->close();
    echo "[consumer] stopped\n";
} catch (\Throwable $e) {
    echo "[consumer][FATAL] " . $e->getMessage() . "\n";
    exit(1);
}