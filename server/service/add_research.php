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
const STATE = 4;

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

$location = get_location_data($research['location']);
if (empty($location)) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: luogo non trovato');
    $response->send();
    return;
}

$announcementApi = new Announcement();
$researchModel = new Research($research['endpoint']);

$researchModel->setLocation($location['name']);
$researchModel->setLocationParameters($location['parameters']);
$researchModel->setOnlyInTitle($research['only_title']);
$researchModel->setQuery($query);
$researchModel->setLastCheckYesterday();

if (!$announcementApi->validate($researchModel)) {
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: ricerca non salvata');
    $response->send();
    return;
}
$researchRepository->save($researchModel);

$response->setMessage('Ricerca salvata');
$response->send();

/**
 * @param string $location
 * @return array
 */
function get_location_data(string $location): array
{
    $parsedLocation = parse_location($location);
    if ($parsedLocation['type'] === STATE) {
        return ['name' => 'Italia', 'parameters' => $parsedLocation['name']];
    }
    $locationApi = new Location();
    $response = $locationApi->getLocation($parsedLocation['name']);
    if ($response->getHttpCode() > 200) {
        return [];
    }
    $data = $response->getData();
    if ($parsedLocation['type'] === NONE) {
        $location = $data[0];
    } else {
        foreach ($data as $location) {
            if (array_key_exists('town', $location) && $parsedLocation['type'] === TOWN) {
                break;
            } elseif (array_key_exists('town', $location)) {
                continue;
            }
            if (array_key_exists('city', $location) && $parsedLocation['type'] === CITY) {
                break;
            } elseif (array_key_exists('city', $location)) {
                continue;
            }
            if (array_key_exists('region', $location) && $parsedLocation['type'] === REGION) {
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
    if (array_key_exists('town', $location)) {
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
    $location = strtolower($location);
    if ($location === 'italia' || $location === 'tutta italia') {
        $location = 'italia';
        $locationType = STATE;
    } elseif (strpos($location, 'regione') !== false) {
        $lastSpacePosition = strrpos($location, ' ');
        $location = substr($location, 0, $lastSpacePosition);
        $locationType = REGION;
    } elseif (strpos($location, ') comune') !== false) {
        $lastSpacePosition = strrpos($location, ' ');
        $location = substr($location, 0, $lastSpacePosition);
        $lastSpacePosition = strrpos($location, ' ');
        $location = substr($location, 0, $lastSpacePosition);
        $locationType = TOWN;
    } elseif (strpos($location, 'e provincia') !== false) {
        $lastSpacePosition = strrpos($location, ' ');
        $location = substr($location, 0, $lastSpacePosition);
        $lastSpacePosition = strrpos($location, ' ');
        $location = substr($location, 0, $lastSpacePosition);
        $locationType = CITY;
    } else {
        $locationType = NONE;
    }
    return ['name' => $location, 'type' => $locationType];
}
