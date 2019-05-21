<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (!isset($post['endpoint'])) {
    $response->setHttpCode(401);
    $response->setMessage('Error: not a subscription');
    $response->send();
    return;
}

$researches = $researchRepository->getResearchesByEndpoint($post['endpoint']);
if (count($researches) == 0) {
    $response->setHttpCode(404);
    $response->setMessage('Researches not found');
    $response->send();
    return;
}

$jsonResearches = [];
for ($i = 0; $i < count($researches); $i++) {
    $jsonResearches[$i] = [];
    $jsonResearches[$i]['region'] = $researches[$i]->getRegion();
    $jsonResearches[$i]['city'] = $researches[$i]->getCity();
    $jsonResearches[$i]['query'] = $researches[$i]->getQuery();
}
$response->setHttpCode(200);
$response->setMessage('Researches found');
$response->setData($jsonResearches);
$response->send();
