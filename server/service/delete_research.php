<?php

use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$post = json_decode(file_get_contents('php://input'), true);

if (!isset($post['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$research = new Research($post['endpoint']);
$research->setQuery($post['query']);
$research->setRegion($post['region']);
$research->setCity($post['city']);

$researchRepository->delete($research);
header('Content-Type: application/json');
echo json_encode('{"status": "announcement deleted"}');