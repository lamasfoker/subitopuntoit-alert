<?php

use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$research = json_decode(file_get_contents('php://input'), true);

if (!isset($research['endpoint'])) {
    echo 'Error: not a subscription';
    return;
}

$researchModel = new Research($research['endpoint']);
$researchModel->setRegion($research['region']);
$researchModel->setCity($research['city']);
$researchModel->setQuery($research['query']);
$researchRepository->save($researchModel);