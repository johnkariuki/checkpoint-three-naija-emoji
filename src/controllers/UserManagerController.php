<?php
namespace NaijaEmoji\Manager;

use Potato\Manager\PotatoModel;
use Carbon\Carbon;
use PDOException;

class UserManagerController extends PotatoModel
{
    protected static $table = "users";

    public static function createUser($request, $response)
    {
        $data = $request->getParsedBody();
        try {
            if (is_array($data) && count(array_diff(['username', 'password'], array_keys($data)))) {
                throw new PDOException("Missing some required fields");
            }
            try {
                $user = self::findRecord([
                    'username' => $data['username'],
                    'password' => hash("sha256", $data['password'])
                ]);
                $response = $response->withStatus(400);
                $message = json_encode([
                    'message' => "User already exists."
                ]);
            } catch (PDOException $e) {
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


        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }
}
