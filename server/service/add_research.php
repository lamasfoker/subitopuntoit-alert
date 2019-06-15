<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Api\Announcement;
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

$api = new Announcement();
$researchModel = new Research($research['endpoint']);

$researchModel->setLocation($research['location']);
$researchModel->setLocationParameters($research['location_parameters']);
$researchModel->setOnlyInTitle($research['only_title']);
$researchModel->setQuery($research['query']);
$researchModel->setLastCheckNow();

if (!$api->validate($researchModel)){
    $response->setHttpCode(404);
    $response->setMessage('ERROR: Research not saved');
    $response->send();
    return;
}
$researchRepository->save($researchModel);

$response->setHttpCode(200);
$response->setMessage('Research saved');
$response->send();