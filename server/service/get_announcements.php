<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (!isset($post['endpoint'])) {
    $response->setHttpCode(401);
    $response->setMessage('Error: not a subscription');
    $response->send();
    return;
}

$announcements = $announcementRepository->getAnnouncementsByEndpoint($post['endpoint']);
if (count($announcements) == 0) {
    $response->setHttpCode(404);
    $response->setMessage('Announcements not found');
    $response->send();
    return;
}

$jsonAnnouncements = [];
for ($i = 0; $i < count($announcements); $i++) {
    $jsonAnnouncements[$i] = $announcements[$i]->getDetails();
}
$response->setHttpCode(200);
$response->setMessage('Announcements found');
$response->setData($jsonAnnouncements);
$response->send();
