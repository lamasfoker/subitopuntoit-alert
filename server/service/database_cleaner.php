<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
set_time_limit(10800);

use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

const NUMBER_OF_USER_ANNOUNCEMENTS_TO_KEEP = 80;

$announcementRepository = new AnnouncementRepository();
$subscriptionRepository = new SubscriptionRepository();
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

foreach ($subscriptionRepository->getSubscriptions() as $subscription) {
    $userAnnouncements = $announcementRepository->getAnnouncementsByEndpoint($subscription->getEndpoint());
    $numberOfUserAnnouncementsToDelete = count($userAnnouncements) - NUMBER_OF_USER_ANNOUNCEMENTS_TO_KEEP;
    for ($index = 0; $index < $numberOfUserAnnouncementsToDelete; $index++) {
        $announcementRepository->delete($userAnnouncements[$index]);
    }
}
