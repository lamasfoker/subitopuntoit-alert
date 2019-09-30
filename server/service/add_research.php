<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Location;
use SubitoPuntoItAlert\Api\Response;
use SubitoPuntoItAlert\Api\Announcement;
use SubitoPuntoItAlert\Database\Model\Research;
use SubitoPuntoItAlert\Database\Repository\ResearchRepository;

const NONE = 0;
const REGION = 1;
const CITY = 2;
const TOWN = 3;

$researchRepository = new ResearchRepository();
$response = new Response();
$research = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('endpoint', $research) ||
    !array_key_exists('location', $research) ||
    !array_key_exists('only_title', $research) ||
    !array_key_exists('query', $research)
) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$query = $research['query'];
if ($query === '') {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: inserisci un termine di ricerca');
    $response->send();
    return;
}

$locationApi = new Location();
$locationInfo = parse_location($research['location']);

$locationResponse = $locationApi->getLocation($locationInfo['name']);
if ($locationResponse->getHttpCode() > 200) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: luogo non trovato');
    $response->send();
    return;
}

$dataLocation = get_location_data($locationResponse->getData(), $locationInfo['type']);

$announcementApi = new Announcement();
$researchModel = new Research($research['endpoint']);

$researchModel->setLocation($dataLocation['name']);
$researchModel->setLocationParameters($dataLocation['parameters']);
$researchModel->setOnlyInTitle($research['only_title']);
$researchModel->setQuery($query);
$researchModel->setLastCheckYesterday();

if (!$announcementApi->validate($researchModel)){
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: ricerca non salvata');
    $response->send();
    return;
}
$researchRepository->save($researchModel);

$response->setMessage('Ricerca salvata');
$response->send();

/**
 * @param array $data
 * @param int $locationType
 * @return array
 */
function get_location_data(array $data, int $locationType): array
{
    if ($locationType === NONE) {
        $location = $data[0];
    } else {
        foreach ($data as $location) {
            if (array_key_exists('town', $location) && $locationType === TOWN) {
                break;
            } elseif (array_key_exists('town', $location)) {
                continue;
            }
            if (array_key_exists('city', $location) && $locationType === CITY) {
                break;
            } elseif (array_key_exists('city', $location)) {
                continue;
            }
            if (array_key_exists('region', $location) && $locationType === REGION) {
                break;
            } elseif (array_key_exists('region', $location)) {
                continue;
            }
        }
    }

    $locationData['parameters'] = $location['region']['friendly_name'];
    $locationData['name'] = $location['region']['value'];
    if (array_key_exists('city', $location)) {
        $locationData['parameters'] .= ' ' . $location['city']['friendly_name'];
        $locationData['name'] = $location['city']['value'];
    }
    if (array_key_exists('town',$location)) {
        $locationData['parameters'] .= ' ' . $location['town']['friendly_name'];
        $locationData['name'] = $location['town']['value'];
    }

    return $locationData;
};

/**
 * @param string $location
 * @return array
 */
function parse_location(string $location): array
{
    if (strpos($location, 'regione') !== false) {
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $locationType = REGION;
    } elseif (
        strpos($location, ') comune') !== false
    ) {
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $locationType = TOWN;
    } elseif (
        strpos($location, 'e provincia') !== false
    ) {
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $last_space_position = strrpos($location, ' ');
        $location = substr($location, 0, $last_space_position);
        $locationType = CITY;
    } else {
        $locationType = NONE;
    }
    return ['name' => $location, 'type' => $locationType];
}
