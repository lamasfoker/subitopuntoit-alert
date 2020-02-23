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

const NUMBER_OF_USER_ANNOUNCEMENTS_TO_KEEP = 50;

$notificationRepository = new NotificationRepository();
$researchRepository = new ResearchRepository();
$announcementRepository = new AnnouncementRepository();
$api = new Announcement();
$sender = new Sender();
$counter = new Counter();
$searchCriteria = new SearchCriteria();
$searchCriteria->setParameterName('endpoint')
    ->setCondition('eq')
    ->setOrderBy(AnnouncementRepository::ID_NAME);
$message = 'Hai dei nuovi annunci!';

/** @var Research $research */
foreach ($researchRepository->get() as $research){
    $response = $api->getAnnouncements($research);
    $research->setLastCheck(get_today_date());
    $researchRepository->save($research);
    $endpoint = $research->getEndpoint();

    if ($response->getHttpCode() !== 200){
        continue;
    }

    foreach ($response->getData() as $detail) {
        $announcement = new AnnouncementModel();
        $announcement->setEndpoint($endpoint)
            ->setDetails(json_encode($detail));
        $announcementRepository->save($announcement);
    }

    $notification = new Notification();
    $notification->setEndpoint($endpoint)
        ->setMessage($message);
    $notificationRepository->save($notification);

    $searchCriteria->setParameterValue($endpoint);
    $numberOfUserAnnouncements = $counter->count(AnnouncementRepository::TABLE_NAME, $searchCriteria);
    $numberOfUserAnnouncementsToDelete = $numberOfUserAnnouncements - NUMBER_OF_USER_ANNOUNCEMENTS_TO_KEEP;
    $userAnnouncements = $announcementRepository->get($searchCriteria);
    for ($index = 0; $index < $numberOfUserAnnouncementsToDelete; $index++) {
        $announcementRepository->deleteById($userAnnouncements->current()->getId());
        $userAnnouncements->next();
    }
}

$searchCriteria->setParameterName('message')
    ->setCondition('eq')
    ->setParameterValue($message);
/** @var Notification $notification */
foreach ($notificationRepository->get($searchCriteria) as $notification) {
    $sender->send($notification);
    $notificationRepository->deleteById($notification->getId());
}
$sender->flushReports();

function get_today_date(): string
{
    date_default_timezone_set('Europe/Rome');
    return date("Y-m-d H:i:s");
}