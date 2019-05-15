<?php

use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$post = json_decode(file_get_contents('php://input'), true);

if (!isset($post['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$researches = $researchRepository->getResearchesByEndpoint($post['endpoint']);
if (count($researches) == 0) {
    header('Content-Type: application/json');
    echo json_encode('{"status": "no researches"}');
    return;
}
$jsonResearches = [];
$jsonResearches['status'] = 'researches';
$jsonResearches['list'] = [];
for ($i = 0; $i < count($researches); $i++) {
    $jsonResearches['list'][$i] = [];
    $jsonResearches['list'][$i]['region'] = $researches[$i]->getRegion();
    $jsonResearches['list'][$i]['city'] = $researches[$i]->getCity();
    $jsonResearches['list'][$i]['query'] = $researches[$i]->getQuery();
}
header('Content-Type: application/json');
echo json_encode($jsonResearches);