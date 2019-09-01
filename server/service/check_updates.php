<?php
declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Api\Announcement;
use SubitoPuntoItAlert\Database\Model\Announcement as AnnouncementModel;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;
use SubitoPuntoItAlert\Exception\MissingSubscriptionException;

$announcementRepository = new AnnouncementRepository();
$researchRepository = new ResearchRepository();
$subscriptionRepository = new SubscriptionRepository();
$api = new Announcement();
$researches = $researchRepository->getAllResearch();
$subscriptions = [];

foreach ($researches as $research){
    $response = $api->getAnnouncement($research);
    $research->setLastCheckToday();
    $researchRepository->save($research);
    $endpoint = $research->getEndpoint();

    if ($response->getHttpCode() !== 200){
        //TODO log something
        continue;
    }

    foreach ($response->getData() as $detail) {
        $announcement = new AnnouncementModel($endpoint);
        $announcement->setDetails(json_encode($detail));
        $announcementRepository->save($announcement);
    }

    try {
        $subscriptionModel = $subscriptionRepository->getSubscription($endpoint);
    } catch (MissingSubscriptionException $e) {
        $researchRepository->deleteByEndpoint($endpoint);
        $announcementRepository->deleteByEndpoint($endpoint);
        continue;
    }
    if (!array_key_exists($endpoint, $subscriptions)) {
        $subscriptions[$endpoint] = new Subscription(
            $endpoint,
            $subscriptionModel->getPublicKey(),
            $subscriptionModel->getAuthToken(),
            $subscriptionModel->getContentEncoding()
        );
    }
}

//TODO: move private key in a secret file
$auth = [
    'VAPID' => [
        'subject' => 'mailto:giacomomoscardini@gmail.com',
        'publicKey' => file_get_contents(__DIR__ . '/../../keys/public_key.txt'),
        'privateKey' => file_get_contents(__DIR__ . '/../../keys/private_key.txt'),
    ],
];

foreach ($subscriptions as $subscription) {
    $webPush = new WebPush($auth);
    $res = $webPush->sendNotification(
        $subscription,
        'Hai dei nuovi annunci!'
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
