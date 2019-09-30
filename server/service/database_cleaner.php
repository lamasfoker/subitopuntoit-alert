<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;

$announcementRepository = new AnnouncementRepository();
$announcements = $announcementRepository->getAllAnnouncements();

foreach ($announcements as $announcement) {
    $jsonDetails = json_decode($announcement->getDetails(), true);
    if (array_key_exists('url', $jsonDetails)) {
        $url = $jsonDetails['url'];
        $response = Requests::head($url);
        if ($response->status_code !== 410) {
            continue;
        }
    }
    $announcementRepository->delete($announcement);
}
