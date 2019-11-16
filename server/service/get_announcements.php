<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Counter;
use SubitoPuntoItAlert\Database\Repository\AnnouncementRepository;
use SubitoPuntoItAlert\Database\SearchCriteria;

$announcementRepository = new AnnouncementRepository();
$response = new Response();
$searchCriteria = new SearchCriteria();
$post = json_decode(file_get_contents('php://input'), true);

if (!array_key_exists('endpoint', $post)) {
    $response->setHttpCode(401)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$searchCriteria->setParameterName('endpoint')
    ->setCondition('eq')
    ->setParameterValue($post['endpoint'])
    ->setOrderBy(AnnouncementRepository::ID_NAME)
    ->setOrderType('DESC');

$jsonAnnouncements = [];
foreach ($announcementRepository->get($searchCriteria) as $announcement) {
    $jsonAnnouncements[] = [
        'id' => $announcement->getId(),
        'details' => $announcement->getDetails()
    ];
}

if (empty($jsonAnnouncements)) {
    $response->setHttpCode(404)
        ->setMessage('Non hai annunci salvati');
} else {
    $response->setData($jsonAnnouncements);
}
$response->send();
