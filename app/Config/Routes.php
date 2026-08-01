<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('dashboard', 'Dashboard::index');
$routes->group('contacts', static function ($routes) {
    $routes->get('/', 'Contacts::index');
    $routes->post('create', 'Contacts::create');
    $routes->post('import', 'Contacts::import');
    $routes->post('delete-duplicates', 'Contacts::deleteDuplicates');
});
$routes->group('campaigns', static function ($routes) {
    $routes->get('/', 'Campaigns::index');
    $routes->get('new', 'Campaigns::new');
    $routes->post('create', 'Campaigns::create');
    $routes->get('(:num)/edit', 'Campaigns::edit/$1');
    $routes->post('(:num)/update', 'Campaigns::update/$1');
    $routes->post('(:num)/test', 'Campaigns::test/$1');
});
$routes->get('analytics', 'Analytics::index');
$routes->get('automations', 'Automations::index');
$routes->get('templates', 'Templates::index');
$routes->get('forms', 'Forms::index');
$routes->get('team', 'Settings::team');
$routes->get('settings', 'Settings::index');
$routes->post('settings', 'Settings::save');
