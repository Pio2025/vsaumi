<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// --- Public auth ---
$routes->get('signup', 'Auth::signupForm');
$routes->post('signup', 'Auth::signup');
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// --- Merchant dashboard (session auth) ---
$routes->group('dashboard', ['filter' => 'merchantAuth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->get('applications', 'ApplicationController::index');
    $routes->get('applications/new', 'ApplicationController::newForm');
    $routes->post('applications/new', 'ApplicationController::create');
    $routes->post('applications/(:num)/subscribe', 'ApplicationController::subscribe/$1');
    $routes->get('transactions', 'Dashboard::transactions');
    $routes->get('payouts', 'Dashboard::payouts');
    $routes->get('subscriptions', 'Dashboard::subscriptions');
});

// --- Demo checkout flow ---
$routes->group('checkout', static function (RouteCollection $routes) {
    $routes->get('(:segment)', 'Checkout::methods/$1');
    $routes->get('(:segment)/pay/(:segment)', 'Checkout::form/$1/$2');
    $routes->post('(:segment)/pay/(:segment)', 'Checkout::process/$1/$2');
    $routes->get('approve/(:segment)', 'Checkout::approve/$1');
    $routes->post('approve/(:segment)', 'Checkout::confirm/$1');
    $routes->get('result/(:segment)', 'Checkout::result/$1');
});

// --- Admin portal ---
$routes->get('admin/login', 'Admin\Auth::loginForm');
$routes->post('admin/login', 'Admin\Auth::login');
$routes->get('admin/logout', 'Admin\Auth::logout');

$routes->group('admin', ['filter' => 'adminAuth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('merchants', 'Admin\Dashboard::merchants');
    $routes->post('merchants/(:num)/approve', 'Admin\Dashboard::approveMerchant/$1');
    $routes->get('merchants/(:num)/edit', 'Admin\Dashboard::editMerchantForm/$1');
    $routes->post('merchants/(:num)/update', 'Admin\Dashboard::updateMerchant/$1');
    $routes->post('merchants/(:num)/delete', 'Admin\Dashboard::deleteMerchant/$1');
    $routes->post('settlement/run', 'Admin\Dashboard::runSettlement');
    $routes->get('payouts', 'Admin\Dashboard::payouts');
    $routes->post('payouts/run', 'Admin\Dashboard::runPayouts');
});

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
