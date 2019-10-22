<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
set_time_limit(10800);

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use Symfony\Component\HttpClient\HttpClient;

$announcementRepository = new AnnouncementRepository();
$client = HttpClient::create();

foreach ($announcementRepository->getAnnouncements() as $announcement) {
    $jsonDetails = json_decode($announcement->getDetails(), true);
    if (array_key_exists('url', $jsonDetails)) {
        $url = $jsonDetails['url'];
        $response = $client->request('HEAD', $url);
        if ($response->getStatusCode() !== 410) {
            continue;
        }
    }
    $announcementRepository->delete($announcement);
}
