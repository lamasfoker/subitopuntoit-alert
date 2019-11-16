<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Announcement;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('id', $post) ||
    !array_key_exists('endpoint', $post)
) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

/** @var Announcement $announcement */
$announcement = $announcementRepository->getById($post['id']);
if ($announcement->getEndpoint() !== $post['endpoint']) {
    $response->setHttpCode(401)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$announcementRepository->deleteById($post['id']);
$response->setMessage('Announcio eliminato')
    ->send();
