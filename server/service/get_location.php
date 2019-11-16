<?php
declare(strict_types=1);

use SubitoPuntoItAlert\Api\Location;
use SubitoPuntoItAlert\Api\Response;

if (!array_key_exists('q', $_GET)) {
    $response = new Response();
    $response->setHttpCode(404)
        ->setMessage('ERRORE: qualcosa è andato storto nella richiesta')
        ->send();
    return;
}

$api = new Location();
$response = $api->getLocation($_GET['q']);
$response->send();