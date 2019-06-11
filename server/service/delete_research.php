<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Research;
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

$research = new Research($post['endpoint']);
$research->setQuery($post['query']);
$research->setRegion($post['region']);
$research->setCity($post['city']);

$researchRepository->delete($research);
$response->setHttpCode(200);
$response->setMessage('Announcement deleted');
$response->send();