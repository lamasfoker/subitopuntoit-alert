<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use Symfony\Component\HttpClient\HttpClient;

$announcementRepository = new AnnouncementRepository();
$announcements = $announcementRepository->getAllAnnouncements();
$client = HttpClient::create();

foreach ($announcements as $announcement) {
    $jsonDetails = json_decode($announcement->getDetails(), true);
    if (array_key_exists('url', $jsonDetails)) {
        $url = $jsonDetails['url'];

        $response = $client->request('GET', $url);
        if ($response->getStatusCode() !== 410) {
            continue;
        }
    }
    $announcementRepository->delete($announcement);
}
