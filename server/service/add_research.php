<?php

use SubitoPuntoItAlert\Api\Response;
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

//TODO: checks the data before import it
$researchModel = new Research($research['endpoint']);
$researchModel->setRegion($research['region']);
$researchModel->setCity($research['city']);
$researchModel->setQuery($research['query']);
$researchRepository->save($researchModel);

$response->setHttpCode(200);
$response->setMessage('Research saved');
$response->send();