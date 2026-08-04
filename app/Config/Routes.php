<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api/v1', static function (RouteCollection $routes) {
    // Public — no merchant credentials yet.
    $routes->post('merchants', 'Api\Merchant::register');
    $routes->post('webhooks/(:segment)', 'Api\Webhook::handle/$1');

    // Requires X-Api-Key / X-Signature headers, see ApiAuthFilter.
    $routes->group('', ['filter' => 'apiAuth'], static function (RouteCollection $routes) {
        $routes->get('merchants/me', 'Api\Merchant::me');
        $routes->post('pay', 'Api\Payment::initiate');
    });
});
