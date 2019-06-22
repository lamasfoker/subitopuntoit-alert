<?php

use SubitoPuntoItAlert\Api\Location;
use SubitoPuntoItAlert\Api\Response;

if (!array_key_exists('q', $_GET)) {
    $response = new Response();
    $response->setHttpCode(404);
    $response->setMessage('ERRORE: qualcosa è andato storto nella richiesta');
    $response->send();
    return;
}

$api = new Location();
$response = $api->getLocation($_GET['q']);
$response->send();