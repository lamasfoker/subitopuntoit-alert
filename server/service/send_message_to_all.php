<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

$subscriptionRepository = new SubscriptionRepository();
$announcementRepository = new AnnouncementRepository();
$researchRepository = new ResearchRepository();

foreach ($subscriptionRepository->getSubscriptions() as $subscriptionModel){
    $subscription = new Subscription(
        $subscriptionModel->getEndpoint(),
        $subscriptionModel->getPublicKey(),
        $subscriptionModel->getAuthToken(),
        $subscriptionModel->getContentEncoding()
    );

    $auth = [
        'VAPID' => [
            'subject' => 'mailto:giacomomoscardini@gmail.com',
            'publicKey' => file_get_contents(__DIR__ . '/../../keys/public_key.txt'),
            'privateKey' => file_get_contents(__DIR__ . '/../../keys/private_key.txt'),
        ],
    ];

    $webPush = new WebPush($auth);
    $res = $webPush->sendNotification(
        $subscription,
        'Ci scusiamo se ieri hai riscontrato malfunzionamenti'
    );
    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();

        if (!$report->isSuccess()) {
            //TODO: log "Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
        if ($report->isSubscriptionExpired()) {
            $subscriptionRepository->delete($endpoint);
            $researchRepository->deleteByEndpoint($endpoint);
            $announcementRepository->deleteByEndpoint($endpoint);
        }
    }
}
