<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (!array_key_exists('endpoint', $post)) {
    $response->setHttpCode(401);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$announcementRepository->setOrder('DESC');
$announcements = $announcementRepository->getAnnouncementsByEndpoint($post['endpoint']);
if (count($announcements) == 0) {
    $response->setHttpCode(404);
    $response->setMessage('Non hai annunci salvati');
    $response->send();
    return;
}

$jsonAnnouncements = [];
for ($i = 0; $i < count($announcements); $i++) {
    $jsonAnnouncements[$i] = $announcements[$i]->getDetails();
}
$response->setData($jsonAnnouncements);
$response->send();
