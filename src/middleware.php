<?php

use NaijaEmoji\Manager\AuthController;

// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$authMiddleware = function ($request, $response, $next) {

    if (is_array($request->getHeader("HTTP_TOKEN")) && count($request->getHeader("HTTP_TOKEN")) === 1) {
        $token = $request->getHeader('HTTP_TOKEN');

        $user = AuthController::findRecord([
            'token' => $token[0]
        ]);

        if (is_array($user) && ! empty($user)) {
            $tokenExpiration = $user['expires'];
            if (time() < $tokenExpiration) {
                //still good to go
                $response = $next($request, $response);
            } else {
                //token is expired
                //delete expired token and retry log in

                AuthController::updateUserToken([
                    'id' => $user['id'],
                    'token' => '',
                    'expires' => ''
                ]);

                $response = $response->withStatus(400);
                $response = $response->withHeader('Content-type', 'application/json');
                $message = json_encode([
                    'message' => 'expired token.'
                ]);
                $response->write($message);
            }
        } else {
            //no user with that token
            $response = $response->withStatus(400);
            $response = $response->withHeader('Content-type', 'application/json');
            $message = json_encode([
                'message' => 'invalid token.'
            ]);
            $response->write($message);
        }
    } else {
        //no or invalid token provided
        $response = $response->withStatus(400);
        $response = $response->withHeader('Content-type', 'application/json');
        $message = json_encode([
            'message' => 'No token provided.'
        ]);
        $response->write($message);
    }

    return $response;
};
