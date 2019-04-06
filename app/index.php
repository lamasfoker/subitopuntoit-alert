<?php

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

require __DIR__.'/../vendor/autoload.php';

$dispatcher = simpleDispatcher(
    function (RouteCollector $r) {
        $r->addRoute('GET', '/update/{region}/{city}/{query}', 'update_handler');
        $r->addRoute('GET', '/search/{region}/{city}/{query}', 'search_handler');
        $r->addRoute('GET', '/index', 'homepage_handler');
        $r->addRoute('GET', '[/]', 'homepage_handler');
    }
);

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
case Dispatcher::NOT_FOUND:
    // ... 404 Not Found
    not_found_handler();
    break;
case Dispatcher::METHOD_NOT_ALLOWED:
    $allowedMethods = $routeInfo[1];
    // ... 405 Method Not Allowed
    break;
case Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $vars = $routeInfo[2];
    call_user_func_array($handler, $vars);
    break;
}

function homepage_handler()
{
    require __DIR__.'/pub/homepage.php';
}

/**
 * @param string $region
 * @param string $city
 * @param string $query
 */
function update_handler($region, $city, $query)
{
    $lastUpdate = '{"itemQuantity": 0, "lastItemDate": "undefined"}';
    $url = 'https://www.subito.it/annunci-'.$region.'/vendita/usato/'.$city.'/?q='.$query;
    $data = getJsonData($url);

    if (!$data) {
        not_found_handler();
        return;
    }
    $total = $data->total;
    if ($total !== 0) {
        $lastUpdate = '{"itemQuantity": '.$total.', "lastItemDate": "'.$data->list[0]->item->date.'"}';
    }
    $lastUpdate = json_decode($lastUpdate);

    var_dump(json_encode($lastUpdate));
}

/**
 * @param string $region
 * @param string $city
 * @param string $query
 */
function search_handler($region, $city, $query)
{
    $searchResults = json_decode('{"0": "undefined"}');
    $url = 'https://www.subito.it/annunci-'.$region.'/vendita/usato/'.$city.'/?q='.$query;
    $data = getJsonData($url);

    if (!$data) {
        not_found_handler();
        return;
    }
    $total = $data->total;
    if ($total !== 0) {
        $searchResults = extractUsefulData($data->list);
    }

    var_dump(json_encode($searchResults));
}

function not_found_handler()
{
    var_dump('404');
}

/**
 * @param  array $data
 * @return JsonSerializable
 */
function extractUsefulData($data)
{
    $extractedData = '{';
    foreach ($data as $key => $result) {
        $result = $result->item;
        $name = '/price';
        $price = isset($result->features->$name)? $result->features->$name->values[0]->value:'undefined';
        $town = $result->geo->town->value;
        $imageUrl = isset($result->images[0]) ? $result->images[0]->scale[4]->secureuri : 'undefined';
        $date = $result->date;
        $name = addslashes($result->subject);
        $url = $result->urls->default;
        $extractedData .= '"'.$key.'": { "name": "'.$name.'", "price": "'.$price.'", "town": "'.$town.'", "imageUrl": "'.$imageUrl.'", "date": "'.$date.'", "url": "'.$url.'"}, ';
    }
    $extractedData = substr($extractedData, 0, -2);
    $extractedData .= ' }';
    return json_decode($extractedData);
}

/**
 * @param  string url
 * @return |null
 */
function getJsonData($url)
{
    $response = Requests::get($url);
    if ($response->status_code !== 200) {
        return null;
    }
    $delimiter1 = strpos($response->body, '__NEXT_DATA__ = ');
    $delimiter2 = strpos($response->body, ';__NEXT_LOADED_PAGES__');
    $data = substr($response->body, $delimiter1 + 16, $delimiter2-$delimiter1-16);
    return json_decode($data)->props->state->items;
}

$regions = ['emilia-romagna', 'umbria', 'molise', 'calabria', 'veneto', 'trentino-alto-adige', 'piemonte', 'lombardia', 'campania', 'sardegna', 'sicilia', 'puglia', 'valle-d-aosta', 'liguria', 'marche', 'friuli-venezia-giulia', 'toscana', 'lazio', 'abruzzo', 'basilicata'];
