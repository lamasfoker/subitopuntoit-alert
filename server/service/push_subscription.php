<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Database\Model\Subscription;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\Repository\SubscriptionRepository;
use SubitoPuntoItAlert\Database\SearchCriteria;
use SubitoPuntoItAlert\Exception\NoSuchEntityException;

$researchRepository = new ResearchRepository();
$announcementRepository = new AnnouncementRepository();
$subscriptionRepository = new SubscriptionRepository();
$searchCriteria = new SearchCriteria();
$post = json_decode(file_get_contents('php://input'), true);
$method = $_SERVER['REQUEST_METHOD'];

if (
    !array_key_exists('endpoint', $post) ||
    !array_key_exists('publicKey', $post) ||
    !array_key_exists('authToken', $post) ||
    ($method === 'POST' && !array_key_exists('contentEncoding', $post))
) {
    echo 'Error: not a subscription';
    return;
}

$searchCriteria->setParameterName('endpoint')
    ->setCondition('eq')
    ->setParameterValue($post['endpoint']);

switch ($method) {
    case 'POST':
        // create a new subscription entry in your database (endpoint is unique)
        $subscription = new Subscription();
        $subscription->setEndpoint($post['endpoint'])
            ->setPublicKey($post['publicKey'])
            ->setContentEncoding($post['contentEncoding'])
            ->setAuthToken($post['authToken']);
        $subscriptionRepository->save($subscription);
        break;
    case 'PUT':
        // update the key and token of subscription corresponding to the endpoint
        try {
            $subscription = $subscriptionRepository->get($searchCriteria)->current();
            $subscription->setPublicKey($post['publicKey'])
                ->setAuthToken($post['authToken']);
            $subscriptionRepository->save($subscription);
        } catch (NoSuchEntityException $e) {
            $researchRepository->delete($searchCriteria);
            $announcementRepository->delete($searchCriteria);
        }
        break;
    case 'DELETE':
        // delete the subscription corresponding to the endpoint
        $subscriptionRepository->delete($searchCriteria);
        $researchRepository->delete($searchCriteria);
        $announcementRepository->delete($searchCriteria);
        break;
    default:
        echo "Error: method not handled";
        return;
}
