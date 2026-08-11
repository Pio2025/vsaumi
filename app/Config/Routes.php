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
    $routes->get('applications/(:num)', 'ApplicationController::view/$1');
    $routes->post('applications/(:num)/subscribe', 'ApplicationController::subscribe/$1');
    $routes->get('applications/(:num)/subscriptions/(:num)/invoice', 'ApplicationController::invoice/$1/$2');
    $routes->get('transactions', 'Dashboard::transactions');
    $routes->get('payouts', 'Dashboard::payouts');
    $routes->get('withdrawals', 'WithdrawalController::index');
    $routes->post('withdrawals/request', 'WithdrawalController::request');
    $routes->get('subscriptions', 'Dashboard::subscriptions');
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings', 'SettingsController::update');
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
    $routes->get('merchants/(:num)', 'Admin\Dashboard::viewMerchant/$1');
    $routes->post('merchants/(:num)/approve', 'Admin\Dashboard::approveMerchant/$1');
    $routes->get('merchants/(:num)/edit', 'Admin\Dashboard::editMerchantForm/$1');
    $routes->post('merchants/(:num)/update', 'Admin\Dashboard::updateMerchant/$1');
    $routes->post('merchants/(:num)/delete', 'Admin\Dashboard::deleteMerchant/$1');
    $routes->get('applications', 'Admin\ApplicationController::index');
    $routes->get('applications/(:num)', 'Admin\ApplicationController::view/$1');
    $routes->get('applications/(:num)/edit', 'Admin\ApplicationController::editForm/$1');
    $routes->post('applications/(:num)/update', 'Admin\ApplicationController::update/$1');
    $routes->post('applications/(:num)/cancel', 'Admin\ApplicationController::cancelSubscription/$1');
    $routes->post('applications/(:num)/activate', 'Admin\ApplicationController::activatePlan/$1');
    $routes->post('settlement/run', 'Admin\Dashboard::runSettlement');
    $routes->get('payouts', 'Admin\Dashboard::payouts');
    $routes->post('payouts/run', 'Admin\Dashboard::runPayouts');
    $routes->get('withdrawals', 'Admin\WithdrawalController::index');
    $routes->post('withdrawals/(:num)/approve', 'Admin\WithdrawalController::approve/$1');
    $routes->post('withdrawals/(:num)/reject', 'Admin\WithdrawalController::reject/$1');
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
