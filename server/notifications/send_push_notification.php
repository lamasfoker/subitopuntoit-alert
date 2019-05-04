<?php

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use SubitoPuntoItAlert\Api\SubitoUpdater;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

// here I'll get the subscription endpoint in the POST parameters
// but in reality, you'll get this information in your database
// because you already stored it (cf. push_subscription.php)
$subscription = Subscription::create(json_decode(file_get_contents('php://input'), true));
$subscriptionRepository = new SubscriptionRepository();
$researchRepository = new ResearchRepository();

$auth = array(
    'VAPID' => array(
        'subject' => 'https://github.com/Minishlink/web-push-php-example/',
        'publicKey' => file_get_contents('keys/public_key.txt'), // don't forget that your public key also lives in app.js
        'privateKey' => file_get_contents('keys/private_key.txt'), // in the real world, this would be in a secret file
    ),
);

$webPush = new WebPush($auth);
$api = new SubitoUpdater();

$message = $api->getAnnouncementUpdate('2019-04-23 15:08:59', 'emilia-romagna', 'reggio-emilia', 'ps4')['status'];

$res = $webPush->sendNotification(
    $subscription,
    $message
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
        foreach ($researchRepository->getResearchesByEndpoint($subscription['endpoint']) as $research){
            $researchRepository->delete($research);
        }
        echo "[x] Subscription expired";
    }
    echo "[x] Subscription not expired";
}
