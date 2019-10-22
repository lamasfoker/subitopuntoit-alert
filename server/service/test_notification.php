<?php
declare(strict_types=1);

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use SubitoPuntoItAlert\Api\Response;

$subscription = Subscription::create(json_decode(file_get_contents('php://input'), true));
$response = new Response();

$auth = array(
    'VAPID' => array(
        'subject' => 'https://github.com/lamasfoker/subitopuntoitalert',
        'publicKey' => getenv('PUBLIC_KEY'),
        'privateKey' => getenv('PRIVATE_KEY'),
    ),
);

$webPush = new WebPush($auth);

$res = $webPush->sendNotification(
    $subscription,
    'Test Notification'
);

foreach ($webPush->flush() as $report) {
    $endpoint = $report->getRequest()->getUri()->__toString();

    if (!$report->isSuccess()) {
        $response->setHttpCode(500);
        $response->setMessage("Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
    }
    if ($report->isSubscriptionExpired()) {
        $subscriptionRepository->delete($endpoint);
        $researchRepository->deleteByEndpoint($endpoint);
        $response->setHttpCode(404);
        $response->setMessage("Subscription expired");
    }
    $response->setHttpCode(200);
    $response->setMessage("Message sent successfully for subscription {$endpoint}");
}

$response->send();
