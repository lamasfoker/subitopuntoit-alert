<?php

require __DIR__ . '/vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('POST', '/push-subscription', 'subscription_handler');
    $r->addRoute('PUT', '/push-subscription', 'subscription_handler');
    $r->addRoute('DELETE', '/push-subscription', 'subscription_handler');
    $r->addRoute('POST', '/test-notification', 'test_notification_handler');
    $r->addRoute('POST', '/add-research', 'add_research_handler');
//    $r->addRoute('GET', '[/]', 'homepage_handler');
});

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
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func_array($handler, $vars);
        break;
}

function test_notification_handler(){
    require __DIR__.'/server/service/test_notification.php';
    return;
}

function subscription_handler(){
    require __DIR__.'/server/service/push_subscription.php';
    return;
}

function add_research_handler(){
    require __DIR__.'/server/service/add_research.php';
    return;
}

function homepage_handler(){
    require __DIR__.'/app/index.html';
    return;
}