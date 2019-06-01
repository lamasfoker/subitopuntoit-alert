<?php

use SubitoPuntoItAlert\Database\Model\Subscription;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

$researchRepository = new ResearchRepository();
$announcementRepository = new AnnouncementRepository();
$subscriptionRepository = new SubscriptionRepository();
$subscription = json_decode(file_get_contents('php://input'), true);

if (!isset($subscription['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // create a new subscription entry in your database (endpoint is unique)
        $subscriptionModel = new Subscription($subscription['endpoint']);
        $subscriptionModel->setPublicKey($subscription['publicKey']);
        $subscriptionModel->setContentEncoding($subscription['contentEncoding']);
        $subscriptionModel->setAuthToken($subscription['authToken']);
        $subscriptionRepository->save($subscriptionModel);
        break;
    case 'PUT':
        // update the key and token of subscription corresponding to the endpoint
        $subscriptionModel = $subscriptionRepository->getSubscription($subscription['endpoint']);
        $subscriptionModel->setPublicKey($subscription['publicKey']);
        $subscriptionModel->setAuthToken($subscription['authToken']);
        $subscriptionRepository->save($subscriptionModel);
        break;
    case 'DELETE':
        // delete the subscription corresponding to the endpoint
        // TODO: implement deleteByEndpoint x2
        $subscriptionRepository->delete($subscription['endpoint']);
        foreach ($researchRepository->getResearchesByEndpoint($subscription['endpoint']) as $research){
            $researchRepository->delete($research);
        }
        foreach ($announcementRepository->getAnnouncementsByEndpoint($subscription['endpoint']) as $announcement){
            $announcementRepository->delete($announcement);
        }
        break;
    default:
        echo "Error: method not handled";
        return;
}
