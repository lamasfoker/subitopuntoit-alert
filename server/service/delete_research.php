<?php

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('endpoint', $post) ||
    !array_key_exists('query', $post) ||
    !array_key_exists('location', $post) ||
    !array_key_exists('location_parameters', $post) ||
    !array_key_exists('is_only_in_title', $post)
) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$research = new Research($post['endpoint']);
$research->setQuery($post['query']);
$research->setLocation($post['location']);
$research->setLocationParameters($post['location_parameters']);
$research->setOnlyInTitle($post['is_only_in_title']);

$researchRepository->delete($research);
$response->setMessage('Ricerca eliminata');
$response->send();
