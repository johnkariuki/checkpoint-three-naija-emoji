<?php

/**
 * @api NaijaEmoji Service
 *
 * @author John Kariuki john.kariuki@andela.com
 *
 * @statuscodes = {
 *              200 - OK
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
use Carbon\Carbon;

/**
 * @route /
 * @method  root (GET) Root URI to Naija emoji API service
 * @requiredParams none
 * @queryParams none
 * @returns JSON data of the request
 */
$app->get('/', function (Request $request, Response $response, array $args) {

    $response = $response->withStatus(200);
    $response = $response->withHeader('Content-type', 'application/json');
    $message = json_encode([
        'message' => 'welcome to the naija-emoji RESTful Api'
    ]);

    return $response->write($message);
});
