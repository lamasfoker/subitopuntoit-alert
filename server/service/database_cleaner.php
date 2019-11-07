<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
set_time_limit(10800);

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use Symfony\Component\HttpClient\HttpClient;

$announcementRepository = new AnnouncementRepository();
date_default_timezone_set('Europe/Rome');
$oneMonthAgo = date("Y-m-d H:i:s",strtotime("-1 months"));;

foreach ($announcementRepository->getAnnouncements() as $announcement) {
    $jsonDetails = json_decode($announcement->getDetails(), true);
    if (array_key_exists('date', $jsonDetails)) {
        $date = $jsonDetails['date'];
        if (strcmp($date, $oneMonthAgo) <= 0) {
            $announcementRepository->delete($announcement);
        }
    }
}
