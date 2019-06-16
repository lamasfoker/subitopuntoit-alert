<?php

use SubitoPuntoItAlert\Api\Location;
use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Api\Announcement;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

$researchRepository = new ResearchRepository();
$response = new Response();
$research = json_decode(file_get_contents('php://input'), true);

if (!isset($research['endpoint'])) {
    $response->setHttpCode(401);
    $response->setMessage('ERROR: subscription not present');
    $response->send();
    return;
}

$locationApi = new Location();
$locationResponse = $locationApi->getLocation(parse_location($research['location']));
if ($locationResponse->getHttpCode() > 200) {
    $response->setHttpCode(404);
    $response->setMessage('ERROR: location not found');
    $response->send();
    return;
}

$dataLocation = $locationResponse->getData()[0];
$locationParameters = $dataLocation['region']['friendly_name'];
$locationName = $dataLocation['region']['value'];
if (array_key_exists('city', $dataLocation)) {
    $locationParameters .= ' ' . $dataLocation['city']['friendly_name'];
    $locationName = $dataLocation['city']['value'];
}
if (array_key_exists('town',$dataLocation)) {
    $locationParameters .= ' ' . $dataLocation['town']['friendly_name'];
    $locationName = $dataLocation['town']['value'];
}

$announcementApi = new Announcement();
$researchModel = new Research($research['endpoint']);

$researchModel->setLocation($locationName);
$researchModel->setLocationParameters($locationParameters);
$researchModel->setOnlyInTitle($research['only_title']);
$researchModel->setQuery($research['query']);
$researchModel->setLastCheckNow();

if (!$announcementApi->validate($researchModel)){
    $response->setHttpCode(404);
    $response->setMessage('ERROR: research not saved');
    $response->send();
    return;
}
$researchRepository->save($researchModel);

$response->setHttpCode(200);
$response->setMessage('Research saved');
$response->send();

/**
 * @param string $location
 * @return string
 */
function parse_location(string $location): string
{
    if (strpos($location, 'regione') !== false) {
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
    }
    if (
        strpos($location, 'e provincia') !== false ||
        strpos($location, ') comune') !== false
    ) {
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
    }
    return $location;
};
