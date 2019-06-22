<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (!array_key_exists('endpoint', $post)) {
    $response->setHttpCode(401);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$researches = $researchRepository->getResearchesByEndpoint($post['endpoint']);
if (count($researches) == 0) {
    $response->setHttpCode(404);
    $response->setMessage('Non hai ricerche salvate');
    $response->send();
    return;
}

$jsonResearches = [];
for ($i = 0; $i < count($researches); $i++) {
    $jsonResearches[$i] = [];
    $jsonResearches[$i]['location'] = $researches[$i]->getLocation();
    $jsonResearches[$i]['location_parameters'] = $researches[$i]->getLocationParameters();
    $jsonResearches[$i]['is_only_in_title'] = $researches[$i]->isOnlyInTitle();
    $jsonResearches[$i]['query'] = $researches[$i]->getQuery();
}
$response->setData($jsonResearches);
$response->send();
