<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Announcement;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('endpoint', $post) ||
    !array_key_exists('details', $post)
) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$announcement = new Announcement($post['endpoint']);
$announcement->setDetails($post['details']);

$announcementRepository->delete($announcement);
$response->setMessage('Announcio eliminato');
$response->send();
