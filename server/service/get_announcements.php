<?php

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$post = json_decode(file_get_contents('php://input'), true);

if (!isset($post['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$announcements = $announcementRepository->getAnnouncementsByEndpoint($post['endpoint']);
if (count($announcements) == 0) {
    header('Content-Type: application/json');
    echo json_encode('{"status": "no announcements"}');
    return;
}
$jsonAnnouncements = [];
$jsonAnnouncements['status'] = 'announcements';
for ($index = 0; $i < count($announcements); $i++) {
    $jsonAnnouncements['list'][$i] = $announcements[$i]->getDetails();
}
header('Content-Type: application/json');
echo json_encode($jsonAnnouncements);