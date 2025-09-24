<?php

namespace App\Config;

use System\Http\Request;
use System\Http\Routing\RouteList;

/** @var RouteList $routes */

//$routes->get('/',                                'HtmlController::getApiSandbox', [
//	'Default'=>"html", 'Formats'=>["html", "json"]
//]);

$routes->get('/', '/swagger/index.html', ['Default'=>"html"]);

/*  ImageController  */

$routes->get('/image/{id:integer}',      'ImageController::getImageById');
$routes->get('/image/{id:integer}/show', 'ImageController::showImageById', [
	'Default'=>"image",
	'Formats'=>["image", "image-base64", "url"]
]);
$routes->get('/images',                  'ImageController::getAllImages', [
	'Formats'=>["json", "xml"],
	'Xml-Element'=>"image"
]);
$routes->get('/images/{query:text}',     'ImageController::getAllImages', [
	'Formats'=>["json", "xml"]
]);
$routes->get('/images-e/{query:text}',   'ImageController::extractAllImages');
$routes->post('/insert/image',           'ImageController::insertImage');

$routes->get('/tag/{id:integer}',                'GetController::getTagById');
$routes->get('/civilian/{id:integer}',           'GetController::getCivilianById');
$routes->get('/tags',                            'GetController::getAllTags', [
	'Formats'=>["json", "xml"], 'Xml-Element'=>"tag",
]);
$routes->get('/tags/{query:text}',               'GetController::getAllTags');
$routes->get('/universes',                       'GetController::getAllUniverses');
$routes->get('/universes/{query:text}',          'GetController::getAllUniverses');
$routes->get('/universes-elements',              'GetController::getAllUniverses');
$routes->get('/universes-elements/{query:text}', 'GetController::getAllUniverses');
$routes->get('/civilians',                       'GetController::getAllCivilians');
$routes->get('/civilians/{query:text}',          'GetController::getAllCivilians');

/*  Naughty List routes  */
$routes->get(   '/naughty-list',                       'NaughtylistController::getNaughtyList');
$routes->get(   '/naughty-list/all',                   'NaughtylistController::getAllNaughtyList');
$routes->get(   '/naughty-list2',                      'NaughtylistController::_getNaughtyList');
$routes->get(   '/naughty-list/scheduled',             'NaughtylistController::getScheduledNaughtyList');
$routes->get(   '/naughty-list/history',               'NaughtylistController::getNaughtyListHistory');
$routes->get(   '/naughty-list/purchased',             'NaughtylistController::getPurchasedNaughtyList');

$routes->post(  '/naughty-list',                       'NaughtylistController::insertNaughtyList');
$routes->post(  '/naughty-list/update/{id:integer}',   'NaughtylistController::updateNaughtyList');
$routes->post(  '/naughty-list/purchase/{id:integer}', 'NaughtylistController::purchaseNaughtyList');

$routes->delete('/naughty-list/{index:integer}',       'NaughtylistController::deleteNaughtyListProduct');


$routes->get('/template/{name:text}', 'GetController::getTemplateByName');
$routes->get('/options', null, [
	'Formats'=>["json", "xml"], 'Parameters'=>['route'], 'Xml-Element'=>"options",
]);

$routes->post('/upload-temp-file/{input_name:text}', 'PostController::uploadTempFile', [
	'Default'=>"text", 'Formats'=>["text"]
]);
$routes->post('/insert/universe',        'PostController::insertUniverse');
$routes->post('/insert/universeElement', 'PostController::insertUniverseElement');
$routes->post('/insert/demon',           'PostController::insertDemon');
$routes->post('/insert/tag',             'PostController::insertTag');
$routes->post('/insert/language',        'PostController::insertWord');
$routes->post('/insert/civilian',        'PostController::insertCivilian');


$routes->delete('/remove-temp-files', 'DeleteController::removeTempFiles', [
	'Default'=>"text", 'Formats'=>["text"]
]);


$routes->options('/signin',   'OptionsController::signIn');
$routes->options('/login',    'OptionsController::login');
$routes->options('/logout',   'OptionsController::logout');
$routes->options('/islogged', 'OptionsController::isLogged');


/*  Debug routes  */
$routes->post('/insert', 'PostController::insertTest');

$routes->post('/insert/test', function(Request $request) {
	return $request->createResponse($request->getData(), true);
}, ['Formats'=>["json", "text"]]);

$routes->match(['GET', 'POST'], '/debug', function(Request $request) {
	return $request->createResponse(['$_GET' => $_GET, '$_POST' => $_POST, '$_FILES' => $_FILES, '$_SERVER' => $_SERVER]);
});


$routes->get('/v1/swagger.json', 'ApiController::swaggerV1');
