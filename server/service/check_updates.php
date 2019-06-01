<?php

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use SubitoPuntoItAlert\Api\SubitoUpdater;
use SubitoPuntoItAlert\Database\Model\Announcement;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

$announcementRepository = new AnnouncementRepository();
$researchRepository = new ResearchRepository();
$subscriptionRepository = new SubscriptionRepository();
$api = new SubitoUpdater();
$researches = $researchRepository->getAllResearch();

foreach ($researches as $research){
    $response = $api->getAnnouncementUpdate(
        '2019-05-09 15:08:59',//TODO: replace with $research->getLastCheck(),
        $research->getRegion(),
        $research->getCity(),
        $research->getQuery()
    );

    $research->setLastCheckNow();
    $researchRepository->save($research);

    if ($response->getHttpCode() !== 200){
        //TODO log something
        return;
    }

    foreach ($response->getData() as $detail) {
        $announcement = new Announcement($research->getEndpoint());
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

    $auth = array(
        'VAPID' => array(
            'subject' => 'https://github.com/Minishlink/web-push-php-example/',
            'publicKey' => file_get_contents('keys/public_key.txt'), // don't forget that your public key also lives in app.js
            'privateKey' => file_get_contents('keys/private_key.txt'), // in the real world, this would be in a secret file
        ),
    );

    $webPush = new WebPush($auth);

    $res = $webPush->sendNotification(
        $subscription,
        $response->getMessage()
    );

    // handle eventual errors here, and remove the subscription from your server if it is expired
    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();

        if ($report->isSuccess()) {
            echo "[v] Message sent successfully for subscription {$endpoint}.";
        } else {
            echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
        if ($report->isSubscriptionExpired()) {
            $subscriptionRepository->delete($endpoint);
            $researchRepository->deleteByEndpoint($endpoint);
            echo "[x] Subscription expired";
        }
        echo "[x] Subscription not expired";
    }

}

$lastCheck = date("Y-m-d H:i:s");