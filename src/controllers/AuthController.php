<?php

namespace NaijaEmoji\Manager;

use Potato\Manager\PotatoModel;
use Carbon\Carbon;
use PDOException;

class AuthController extends PotatoModel
{
    protected static $table = "users";

    /**
     * @route POST /auth/register
     *
     * @method   /auth/register (POST) Register A new user with
     * username and password
     *
     * @requiredParams none
     * @queryParams  none
     *
     * @return JSON     Message of success or error in registering user
     */
    public static function createUser($request, $response)
    {
        $data = $request->getParsedBody();
        try {
            if (is_array($data) && count(array_diff(['username', 'password'], array_keys($data)))) {
                throw new PDOException("Missing some required fields");
            }

            if (! self::testEmptyElements($request->getParsedBody(), ['username', 'password'])) {

                $response = $response->withStatus(400);
                $message = json_encode([
                    "message" => "Empty values provided."
                ]);
            } else {
                $user = self::findRecord([
                    'username' => $data['username'],
                    'password' => hash("sha256", $data['password'])
                ]);

                if (is_array($user) && ! empty($user)) {
                    $response = $response->withStatus(400);
                    $message = json_encode([
                        'message' => "User already exists."
                    ]);
                } else {
                    // register user
                    $user = new self();
                    $user->username = $data['username'];
                    $user->password = hash("sha256", $data['password']);

                    if ($user->save()) {
                        $response = $response->withStatus(201);
                        $message = json_encode([
                            'message' => "User successfully registered."
                        ]);
                    } else {
                        $response = $response->withStatus(400);
                        $message = json_encode([
                            'message' => "Error registering user."
                        ]);
                    }
                }
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

    /**
     * @route POST /auth/login
     *
     * @method   /auth/login (POST) Authenticate and generate a token for the user
     *
     * @requiredParams none
     * @queryParams  none
     *
     * @return JSON     Generated token
     */
    public static function loginUser($request, $response)
    {
        $data = $request->getParsedBody();
        try {
            if (is_array($data) && count(array_diff(['username', 'password'], array_keys($data)))) {
                throw new PDOException("Missing some required fields");
            }

            $user = self::findRecord([
                'username' => $data['username'],
                'password' => hash("sha256", $data['password'])
            ]);
            if (! self::testEmptyElements($request->getParsedBody(), ['username', 'password'])) {

                $response = $response->withStatus(400);
                $message = json_encode([
                    "message" => "Empty values provided."
                ]);
            } else {
                if (is_array($user) && ! empty($user)) {
                    $token = hash("sha256", $data['username'] . md5(3.142) . time() . rand(1, 1001));

                    $tokenData = [
                        'token' => $token,
                        'expires' => time() + 86400,
                        'id' => $user[id]
                    ];

                    if (self::updateUserToken($tokenData)) {
                        $response = $response->withStatus(200);
                        $message = json_encode([
                            'message' => 'login successful',
                            'token' => $token
                        ]);
                    } else {
                        $response = $response->withStatus(400);
                        $message = json_encode([
                            'message' => 'Error authenticating user.'
                        ]);
                    }
                } else {
                    $response = $response->withStatus(400);
                    $message = json_encode([
                        'message' => "Invalid login credentials."
                    ]);
                }
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

    /**
     * @route GET /auth/logout
     *
     * @method   /auth/login (GET) Delete a user token
     *
     * @requiredParams none
     * @queryParams  none
     *
     * @return JSON     Message of succes or error in loggging a user out
     */
    public static function logoutUser($request, $response)
    {
        try {
            $tokenInfo = self::findRecord([
                'token' => $request->getHeader("HTTP_TOKEN")[0]
            ]);

            $deleteToken = [
                'id' => $tokenInfo['id'],
                'token' => "",
                'expires' => ""
            ];

            if (self::updateUserToken($deleteToken)) {
                $response = $response->withStatus(200);
                $message = json_encode([
                    'message' => "successfully logged out."
                ]);
            } else {
                $response = $response->withStatus(400);
                $message = json_encode([
                    'message' => "error logging out."
                ]);
            }

        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

    /**
     * Update the user's token details
     * @param  array  $tokenData array of data to be updated
     * @return boolean      true or false
     */
    public static function updateUserToken(array $tokenData)
    {
        if (is_array($tokenData)) {
            $updateToken = self::find($tokenData['id']);

            $updateToken->token = $tokenData['token'];
            $updateToken->expires = $tokenData['expires'];
            if ($updateToken->save()) {
                return true;
            }
        }

        return false;
    }

    /**
     * [testEmptyElements description]
     * @param  array  $data           values passed by the user
     * @param  array  $requiredFields required fields
     * @return boolean                true or false
     */
    public static function testEmptyElements(array $data, array $requiredFields)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $requiredFields)) {
                //check if is empty
                if (trim($value) == "") {
                    return false;
                }
            }
        }
        return true;
    }
}
