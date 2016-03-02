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
use \NaijaEmoji\Manager\AuthController;

/**
 * @route GET /
 *
 * @method  root (GET) Root URI to Naija emoji API service.
 *
 * @requiredParams none
 * @queryParams none
 *
 * @return JSON     welcome to the naija-emoji RESTful Api.
 */
$app->get('/', function (Request $request, Response $response, array $args) {

     return EmojiManagerController::root($request, $response, $args);
});

/**
 * @route POST /auth/register
 *
 * @method   /auth/register (POST) Register A new user with
 * username and password.
 *
 * @requiredParams none
 * @queryParams  none
 *
 * @return JSON     Message of success or error in registering user
 */
    $app->post('/auth/register', function (Request $request, Response $response, array $args) {

        return AuthController::createUser($request, $response);
    });

/**
 * @route POST /auth/login
 *
 * @method   /auth/login (POST) Authenticate and generate a token for the user.
 *
 * @requiredParams none
 * @queryParams  none
 *
 * @return JSON     Generated token
 */
    $app->post('/auth/login', function (Request $request, Response $response, array $args) {

        return AuthController::loginUser($request, $response);
    });

/**
 * @route GET /auth/logout
 *
 * @method   /auth/login (GET) Delete a user token.
 *
 * @requiredParams none
 * @queryParams  none
 *
 * @return JSON     Message of succes or error in loggging a user out.
 */
    $app->get('/auth/logout', function (Request $request, Response $response) {

        return AuthController::logoutUser($request, $response);
    })->add($authMiddleware);

/**
 * @route GET /emojis
 *
 * @method  emojis (GET) Return all records of emojis from database.
 *
 * @requiredParams none
 * @queryParams none
 *
 * @return JSON     List of all emojis
 */
    $app->get('/emojis', function (Request $request, Response $response, array $args) {

        return EmojiManagerController::getEmojis($request, $response, $args);
    });

/**
 * @route GET /emojis/{id}
 *
 * @method  emojis/{id}(GET, id) Return a record whose primary key matches provided id.
 *
 * @requiredParams id
 * @queryParams id
 *
 * $return JSON     data for a record whose primary key matches provided id.
 */
    $app->get('/emojis/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

        return EmojiManagerController::getEmoji($request, $response, $args);
    });

/**
 * Group all standard URI groups that require token authentication.
 *
 * POST /emojis
 * PUT /emojis/{id}
 * PATCH /emojis/{id}
 * DELETE /emojis/{id}
 */
    $app->group('/emojis', function () use ($app) {
        /**
     * @route POST /emojis
     *
     * @method  /emojis (POST) Add a new emoji record.
     *
     * @requiredParams none
     * @queryParams none
     *
     * @return  JSON    data of success or failure in adding new record.
     */
        $app->post('', function (Request $request, Response $response, array $args) {

            return EmojiManagerController::postEmoji($request, $response, $args);
        });

    /**
     * @route PUT /emojis/{id}
     *
     * @method  /emojis/{id} (PUT, id) Update all fields in an emoji record.
     *
     * @requiredParams id
     * @queryParams id
     *
     * @return JSON     data of success or failure of put request activity.
     */
        $app->put('/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

            return EmojiManagerController::putEmoji($request, $response, $args);
        });

    /**
     * @route PATCH /emojis/{id}
     *
     * @method  /emojis/{id} (PATCH, id) Update specific field in an emoji record.
     *
     * @requiredParams id
     * @queryParams id
     *
     * @return JSON     data of success or failure of put request activity.
     */

        $app->patch('/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

            return EmojiManagerController::patchEmoji($request, $response, $args);
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
        $app->delete('/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

            return EmojiManagerController::deleteEmoji($request, $response, $args);
        });
    })->add($authMiddleware);

    /**
     * @route GET /emojis/{field}/{name}
     *
     * @method   [/emojis/{field}/{name}(GET field,name) Search all records
     * whose fields match a certain name.
     *
     * @requiredParams field, name
     * @queryParams field, name
     *
     * @return JSON All records that match the criteria
     */
    $app->get('/emojis/{field}/{name}', function (Request $request, Response $response, array $args) {

        return EmojiManagerController::searchCategory($request, $response, $args);
    });
