<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
set_time_limit(1800);

use SubitoPuntoItAlert\Api\Announcement;
use SubitoPuntoItAlert\Database\Counter;
use SubitoPuntoItAlert\Database\Model\Notification;
use SubitoPuntoItAlert\Database\Model\Announcement as AnnouncementModel;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\NotificationRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\SearchCriteria;
use SubitoPuntoItAlert\Notification\Sender;

$notificationRepository = new NotificationRepository();
$researchRepository = new ResearchRepository();
$announcementRepository = new AnnouncementRepository();
$api = new Announcement();
$sender = new Sender();
date_default_timezone_set('Europe/Rome');
$counter = new Counter();
$searchCriteria = new SearchCriteria();
$searchCriteria->setParameterName('endpoint')
    ->setCondition('eq')
    ->setOrderBy(AnnouncementRepository::ID_NAME);

/** @var Research $research */
foreach ($researchRepository->get() as $research){
    $response = $api->getAnnouncements($research);
    $today = date("Y-m-d H:i:s");
    $research->setLastCheck($today);
    $researchRepository->save($research);
    $endpoint = $research->getEndpoint();

    if ($response->getHttpCode() !== 200){
        continue;
    }

    foreach ($response->getData() as $detail) {
        $announcement = new AnnouncementModel($endpoint);
        $announcement->setDetails(json_encode($detail));
        $announcementRepository->save($announcement);
    }

    $notificationRepository->save(new Notification($endpoint));

    $searchCriteria->setParameterValue($endpoint);
    $numberOfUserAnnouncements = $counter->count(AnnouncementRepository::TABLE_NAME, $searchCriteria);
    $numberOfUserAnnouncementsToDelete = $numberOfUserAnnouncements - NUMBER_OF_USER_ANNOUNCEMENTS_TO_KEEP;
    $userAnnouncements = $announcementRepository->get($searchCriteria);
    for ($index = 0; $index < $numberOfUserAnnouncementsToDelete; $index++) {
        $announcementRepository->deleteById($userAnnouncements[$index]->getId());
    }
}

try {
    /** @var Notification $notification */
    foreach ($notificationRepository->get() as $notification) {
        $notification->setMessage("Hai dei nuovi annunci!");
        $sender->send($notification);
        $notificationRepository->deleteById($notification->getId());
    }
    $sender->flushReports();
} catch (ErrorException $e) {
    $notificationRepository->delete();
}
