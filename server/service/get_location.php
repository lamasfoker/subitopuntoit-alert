<?php

use SubitoPuntoItAlert\Api\Location;

$query = '';
if (array_key_exists('q', $_GET)) {
    $query = $_GET['q'];
}

$api = new Location();
$response = $api->getLocation($query);
$response->send();