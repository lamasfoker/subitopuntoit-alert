<?php

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use SubitoPuntoItAlert\Api\Response;

// here I'll get the subscription endpoint in the POST parameters
// but in reality, you'll get this information in your database
// because you already stored it (cf. push_subscription.php)
$subscription = Subscription::create(json_decode(file_get_contents('php://input'), true));
$response = new Response();

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
    'Test Notification'
);
// TODO: deletes this code
// handle eventual errors here, and remove the subscription from your server if it is expired
foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();

    if ($report->isSuccess()) {
        $response->setHttpCode(200);
        $response->setMessage("Message sent successfully for subscription {$endpoint}");
    } else {
        $response->setHttpCode(500);
        $response->setMessage("Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
    }
    if ($report->isSubscriptionExpired()) {
        $subscriptionRepository->delete($endpoint);
        $researchRepository->deleteByEndpoint($endpoint);
        $response->setHttpCode(404);
        $response->setMessage("Subscription expired");
    }
}

$response->send();
