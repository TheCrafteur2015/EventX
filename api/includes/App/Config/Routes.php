<?php

namespace App\Config;

use System\Http\Routing\RouteList;

/** @var RouteList $routes */

$routes->get('/', 'MainController::getRoutes');

//$routes->post('/login', '');
//$routes->post('/login/register', '');

$routes->get('/events', 'EventController::getAll');
$routes->get('/event/id/{id:integer}', 'EventController::getById');
$routes->get('/event/search/{text:text}', 'EventController::search');
$routes->get('/events/created-by-user/{id:integer}', 'EventController::createdByUser');

$routes->get('/users', 'UserController::getAll');
$routes->get('/user/id/{id:integer}', 'UserController::getById');
$routes->get('/user/search/{name:text}', 'UserController::search');
$routes->get('/user/profile/{id:integer}', 'UserController::getProfile');
$routes->match([POST, "PUT"], '/user/profile/{id:integer}', 'UserController::setProfile');
$routes->post('/user/create_event', 'UserController::createEvent');

$routes->post('/user/register', 'UserController::registerToEvent');
$routes->delete('/user/unregister', 'UserController::unregisterToEvent');
$routes->post('/user/wishlist', 'UserController::wishlistEvent');
$routes->delete('/user/un-wishlist', 'UserController::unwishlistEvent');

$routes->get('/roles', 'RoleController::getAll');
$routes->put('/role/{id:integer}', 'RoleController::getById');
$routes->put('/role/{id:integer}/users', 'RoleController::getUsersByRole');
