<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$post = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('endpoint', $post) ||
    !array_key_exists('id', $post)
) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

/** @var Research $research */
$id = (int) $post['id'];
$research = $researchRepository->getById($id);
if ($research->getEndpoint() !== $post['endpoint']) {
    $response->setHttpCode(401)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$researchRepository->deleteById($id);
$response->setMessage('Ricerca eliminata')
    ->send();
