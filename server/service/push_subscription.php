<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Subscription;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;

$researchRepository = new ResearchRepository();
$announcementRepository = new AnnouncementRepository();
$subscriptionRepository = new SubscriptionRepository();
$subscription = json_decode(file_get_contents('php://input'), true);
$method = $_SERVER['REQUEST_METHOD'];
$response = new Response();

if (
    !array_key_exists('endpoint', $subscription) ||
    !array_key_exists('publicKey', $subscription) ||
    !array_key_exists('authToken', $subscription) ||
    ($method === 'POST' && !array_key_exists('contentEncoding', $subscription))
) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

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
        $subscriptionRepository->delete($subscription['endpoint']);
        $researchRepository->deleteByEndpoint($subscription['endpoint']);
        $announcementRepository->deleteByEndpoint($subscription['endpoint']);
        break;
    default:
        $response->setHttpCode(405);
        $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
        $response->send();
        return;
}

$response->send();
