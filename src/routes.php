<?php

/**
 * @api NaijaEmoji Service
 *
 * @author John Kariuki john.kariuki@andela.com
 *
 * @statuscodes = {
 *     200 - OK
 *     201 - Created
 *     204 - No content
 *     304 - Not Modified
 *     400 - Bad Request
 *     401 - Not authorized
 *     404 - Not Found
 * }
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \NaijaEmoji\Manager\EmojiManagerController;

/**
 * @route GET /
 *
 * @method  root (GET) Root URI to Naija emoji API service.
 *
 * @requiredParams none
 * @queryParams none
 *
 * @return JSON data of the request.
 */
$app->get('/', function (Request $request, Response $response, array $args) {

     EmojiManagerController::root($request, $response, $args);
});

/**
 * @route GET /emojis
 *
 * @method  emojis (GET) Return all records of emojis from database.
 *
 * @requiredParams none
 * @queryParams none
 *
 * @return JSON data of all emoji records.
 */
$app->get('/emojis', function (Request $request, Response $response, array $args) {

    EmojiManagerController::getEmojis($request, $response, $args);
});

/**
 * @route GET /emojis/{id}
 *
 * @method  emojis/{id}(GET, id) Return a record whose primary key matches provided id.
 *
 * @requiredParams id
 * @queryParams id
 *
 * $return JSON data for a record whose primary key matches provided id.
 */
$app->get('/emojis/{id}', function (Request $request, Response $response, array $args) {

    EmojiManagerController::getEmoji($request, $response, $args);
});

/**
 * @route POST /emojis
 *
 * @method  /emojis (POST) Add a new emoji record.
 *
 * @requiredParams none
 * @queryParams none
 *
 * @return  JSON data of success or failure in adding new record.
 */
$app->post('/emojis', function (Request $request, Response $response, array $args) {
    
    EmojiManagerController::postEmoji($request, $response, $args);
});

/**
 * @route PUT /emojis/{id}
 *
 * @method  /emojis/{id} (PUT, id) Update all fields in an emoji record.
 *
 * @requiredParams id
 * @queryParams id
 *
 * @return JSON data of success or failure of put request activity.
 */
$app->put('/emojis/{id}', function (Request $request, Response $response, array $args) {

    EmojiManagerController::putEmoji($request, $response, $args);
});

/**
 * @route PATCH /emojis/{id}
 *
 * @method  /emojis/{id} (PATCH, id) Update specific field in an emoji record.
 *
 * @requiredParams id
 * @queryParams id
 *
 * @return JSON data of success or failure of put request activity.
 */

$app->patch('/emojis/{id}', function (Request $request, Response $response, array $args) {

    EmojiManagerController::patchEmoji($request, $response, $args);
});

/**
 * @route DELETE /emojis/{id}
 *
 * @method  /emojis/{id} (DELETE, id) Delete an emoji record.
 *
 * @requiredParams id
 * @queryParams id
 *
 * @return Delete an emoji record.
 */
$app->delete('/emojis/{id}', function (Request $request, Response $response, array $args) {

    EmojiManagerController::deleteEmoji($request, $response, $args);
});
