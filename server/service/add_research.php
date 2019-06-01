<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Api\SubitoUpdater;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$research = json_decode(file_get_contents('php://input'), true);

if (!isset($research['endpoint'])) {
    $response->setHttpCode(401);
    $response->setMessage('Error: not a subscription');
    $response->send();
    return;
}

$api = new SubitoUpdater();
$apiResponse = $api->getAnnouncementUpdate(
    date("Y-m-d H:i:s"),
    $research['region'],
    $research['city'],
    $research['query']
);

if ($apiResponse->getHttpCode() >= 400){
    $response->setHttpCode(404);
    $response->setMessage('ERROR: Research not saved');
    $response->send();
    return;
}

$researchModel = new Research($research['endpoint']);
$researchModel->setRegion($research['region']);
$researchModel->setCity($research['city']);
$researchModel->setQuery($research['query']);
$researchModel->setLastCheckNow();
$researchRepository->save($researchModel);

$response->setHttpCode(200);
$response->setMessage('Research saved');
$response->send();