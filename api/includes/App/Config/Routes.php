<?php

namespace App\Config;

use System\Http\Routing\RouteList;

/** @var RouteList $routes */

$routes->get('/', 'MainController::getRoutes');

//$routes->post('/login/register', '');

$routes->get('/events', 'EventController::getAll');
$routes->get('/event/id/{id:integer}', 'EventController::getById');
$routes->get('/event/search/{text:text}', 'EventController::search');
$routes->get('/events/created-by-user/{id:integer}', 'EventController::createdByUser');

//$routes->get('/users/all', '');
//$routes->get('/user/id/{id:integer}', '');
//$routes->get('/user/search/{name:text}', '');
//$routes->get('/user/profile/{id:integer}', '');
//$routes->match([POST, "PUT"], '/user/profile', '');
//$routes->post('/user/create_event', '');
//
//$routes->post('/user/register', '');
//$routes->delete('/user/unregister', '');
//$routes->post('/user/wishlist', '');
//$routes->delete('/user/un-wishlist', '');
//
//$routes->get('/roles', '');
//$routes->put('/role/{id_role:integer}/user/{id_user:integer}', '');
