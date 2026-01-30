<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
$port = getenv('RABBITMQ_PORT') ?: 5672;
$user = getenv('RABBITMQ_USER') ?: 'toubi';
$pass = getenv('RABBITMQ_PASS') ?: 'toubi';

$exchange = 'toubilib.events';
$exchangeType = 'topic';
$mailQueue = 'toubilib.mail.queue';
$routingKey = 'rdv.created.patient.email';

$connection = new AMQPStreamConnection($host, $port, $user, $pass);
$channel = $connection->channel();

$channel->exchange_declare($exchange, $exchangeType, false, true, false);

$channel->queue_declare($mailQueue, false, true, false, false);
$channel->queue_bind($mailQueue, $exchange, 'rdv.*.*.email');

$payload = [
    'event_id' => bin2hex(random_bytes(16)),
    'event_type' => 'rdv.created',
    'aggregate' => 'rdv',
    'aggregate_id' => 123,
    'occurred_at' => (new DateTime())->format(DateTime::ATOM),
    'payload' => [
        'rdvId' => 123,
        'start' => '2026-02-01T09:00:00+00:00',
        'end' => '2026-02-01T09:20:00+00:00',
        'patient' => ['id' => 45, 'email' => 'patient@example.test', 'phone' => '+33102030405'],
        'praticien' => ['id' => 12, 'email' => 'doc@example.test', 'phone' => '+33111222334']
    ],
    'version' => 1
];

$msg = new AMQPMessage(json_encode($payload), [
    'content_type' => 'application/json',
    'delivery_mode' => 2
]);

$channel->basic_publish($msg, $exchange, $routingKey);

echo "Evenement publie : {$routingKey}\n";

$channel->close();
$connection->close();