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
const ITALY = 4;

$researchRepository = new ResearchRepository();
$response = new Response();
$request = json_decode(file_get_contents('php://input'), true);

if (
    !array_key_exists('endpoint', $request) ||
    !array_key_exists('location', $request) ||
    !array_key_exists('only_title', $request) ||
    !array_key_exists('query', $request)
) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$query = $request['query'];
if ($query === '') {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: inserisci un termine di ricerca')
        ->send();
    return;
}

$location = get_location_data($request['location']);
if (empty($location)) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: luogo non trovato')
        ->send();
    return;
}

$announcementApi = new Announcement();
$research = new Research($request['endpoint']);
date_default_timezone_set('Europe/Rome');
$yesterday = date("Y-m-d H:i:s",strtotime("-1 days"));

$research->setLocation($location['name'])
    ->setLocationParameters($location['parameters'])
    ->setOnlyInTitle($request['only_title'])
    ->setQuery($query)
    ->setLastCheck($yesterday);

if (!$announcementApi->validate($research)) {
    $response->setHttpCode(404)
        ->setMessage('ERRORE: ricerca non salvata')
        ->send();
    return;
}
$researchRepository->save($research);

$response->setMessage('Ricerca salvata')
    ->send();

/**
 * @param string $location
 * @return array
 */
function get_location_data(string $location): array
{
    $parsedLocation = parse_location($location);
    if ($parsedLocation['type'] === ITALY) {
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
        $locationType = ITALY;
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
