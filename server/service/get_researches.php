<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;
use SubitoPuntoItAlert\Database\SearchCriteria;

$researchRepository = new ResearchRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);
$searchCriteria = new SearchCriteria();

if (!array_key_exists('endpoint', $post)) {
    $response->setHttpCode(401)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$searchCriteria->setParameterName('endpoint')
    ->setCondition('eq')
    ->setParameterValue($post['endpoint']);

$jsonResearches = [];
foreach ($researchRepository->get($searchCriteria) as $research) {
    $jsonResearches[] = [
        'id' => $research->getId(),
        'location' => $research->getLocation(),
        'location_parameters' => $research->getLocationParameters(),
        'is_only_in_title' => $research->isOnlyInTitle(),
        'query' => $research->getQuery()
    ];
}

if (empty($jsonResearches)) {
    $response->setHttpCode(404)
        ->setMessage('Non hai ricerche salvate');
} else {
    $response->setData($jsonResearches);
}
$response->send();
