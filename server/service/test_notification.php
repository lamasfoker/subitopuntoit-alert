<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Notification;
use SubitoPuntoItAlert\Notification\Sender;

$post = json_decode(file_get_contents('php://input'), true);
$response = new Response();
$sender = new Sender();
$notification = new Notification();

if (!array_key_exists('endpoint', $post)) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$notification->setEndpoint($post['endpoint'])
    ->setMessage('Test Notification');
try {
    $sender->send($notification);
    $sender->flushReports();
} catch (Exception $e) {
    $response->setHttpCode(500)
        ->setMessage($e->getMessage())
        ->send();
    return;
}

$response->setHttpCode(200)
    ->setMessage('Notifica inviata con successo')
    ->send();
