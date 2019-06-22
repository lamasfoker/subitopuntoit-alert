<?php

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Api\Announcement;
use SubitoPuntoItAlert\Database\Model\Announcement as AnnouncementModel;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

$announcementRepository = new AnnouncementRepository();
$researchRepository = new ResearchRepository();
$subscriptionRepository = new SubscriptionRepository();
$api = new Announcement();
$researches = $researchRepository->getAllResearch();

foreach ($researches as $research){
    $research->setLastCheck('2019-06-09 15:08:59'); //TODO: delete this line
    $response = $api->getAnnouncement($research);
    $research->setLastCheckNow();
    $researchRepository->save($research);

    if ($response->getHttpCode() !== 200){
        //TODO log something
        return;
    }

    foreach ($response->getData() as $detail) {
        $announcement = new AnnouncementModel($research->getEndpoint());
        $announcement->setDetails(json_encode($detail));
        $announcementRepository->save($announcement);
    }

    $subscriptionModel = $subscriptionRepository->getSubscription($research->getEndpoint());
    $subscription = new Subscription(
        $subscriptionModel->getEndpoint(),
        $subscriptionModel->getPublicKey(),
        $subscriptionModel->getAuthToken(),
        $subscriptionModel->getContentEncoding()
    );

    //TODO: move private key in a secret file
    $auth = [
        'VAPID' => [
            'subject' => 'mailto:giacomomoscardini@gmail.com',
            'publicKey' => file_get_contents('keys/public_key.txt'),
            'privateKey' => file_get_contents('keys/private_key.txt'),
        ],
    ];

    $webPush = new WebPush($auth);
    $res = $webPush->sendNotification(
        $subscription,
        $response->getMessage()
    );

    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();

        if (!$report->isSuccess()) {
            //TODO: log "Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
        if ($report->isSubscriptionExpired()) {
            $subscriptionRepository->delete($endpoint);
            $researchRepository->deleteByEndpoint($endpoint);
        }
    }
}
