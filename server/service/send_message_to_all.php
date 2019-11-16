<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';
set_time_limit(3600);

use SubitoPuntoItAlert\Database\Model\Notification;
use SubitoPuntoItAlert\Database\Repository\NotificationRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;
use SubitoPuntoItAlert\Notification\Sender;

$notificationRepository = new NotificationRepository();
$subscriptionRepository = new SubscriptionRepository();
$sender = new Sender();

foreach ($subscriptionRepository->get() as $subscription){
    $notificationRepository->save(new Notification($subscription->getEndpoint()));
}

try {
    /** @var Notification $notification */
    foreach ($notificationRepository->get() as $notification) {
        $notification->setMessage("Ci scusiamo se ieri hai riscontrato malfunzionamenti");
        $sender->send($notification);
        $notificationRepository->deleteById($notification->getId());
    }
    $sender->flushReports();
} catch (ErrorException $e) {
    $notificationRepository->delete();
}