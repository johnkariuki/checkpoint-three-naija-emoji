<?php

namespace NaijaEmoji\Manager;

use Potato\Manager\PotatoModel;
use Carbon\Carbon;
use PDOException;

class EmojiManagerController extends PotatoModel
{
    protected static $table = "emojis";

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
    public static function root($request, $response, $args = null)
    {
        $response = $response->withStatus(200);
        $response = $response->withHeader('Content-type', 'application/json');
        $message = json_encode([
            'message' => 'welcome to the naija-emoji RESTful Api'
        ]);

        return $response->write($message);
    }

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
    public static function getEmojis($request, $response, $args)
    {
        try {
            $emojis = self::getAll();

            if (count($emojis) > 0) {
                $response = $response->withStatus(200);
            } else {
                $response = $response->withStatus(204);
            }

            $message = json_encode([
                'message' => $emojis
            ]);

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
     * @route GET /emojis/{id}
     *
     * @method  emojis/{id}(GET, id) Return a record whose primary key matches provided id.
     *
     * @requiredParams id
     * @queryParams id
     *
     * $return JSON data for a record whose primary key matches provided id.
     */
    public static function getEmoji($request, $response, $args)
    {
        try {
            $emoji = EmojiManagerController::findRecord($args['id']);

            if ($emoji) {
                $response = $response->withStatus(200);
                $message = json_encode([
                    'message' => $emoji
                ]);
            } else {
                $response = $response->withStatus(400);
                $message = json_encode([
                    'message' => "no emoji found"
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
     * @route POST /emojis
     *
     * @method  /emojis (POST) Add a new emoji record.
     *
     * @requiredParams none
     * @queryParams none
     *
     * @return  JSON data of success or failure in adding new record.
     */
    public static function postEmoji($request, $response, $args)
    {
        $data = $request->getParsedBody();
        try {
            if (is_array($data) && count(array_diff(['name', 'char', 'keywords', 'category'], array_keys($data)))) {
                throw new PDOException("Missing some required fields");
            } else {
                $emoji = new self();

                $emoji->name = $data["name"];
                $emoji->char = $data["char"];
                $emoji->keywords = json_encode(explode(",", $data["keywords"]));
                $emoji->category = $data["category"];
                $emoji->date_created = Carbon::now()->toDateTimeString();
                $emoji->date_modified = Carbon::now()->toDateTimeString();
                $emoji->created_by = $data["created_by"];

                if ($emoji->save()) {
                    $response = $response->withStatus(200);
                    $message = json_encode([
                        "message" => "Emoji added succesfully."
                    ]);
                } else {
                    $response = $response->withStatus(304);
                    $message = json_encode([
                        "message" => "Error adding emoji."
                    ]);
                }
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

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
    public static function putEmoji($request, $response, $args)
    {
        try {
            $emoji = self::find($args['id']);

            if (count(array_diff(['name', 'char', 'keywords', 'category'], array_keys($request->getParsedBody())))) {
                throw new PDOException("Missing some required fields");
            } else {
                foreach ($request->getParsedBody() as $key => $value) {
                    $emoji->$key = $key === "keywords" ? json_encode(explode(",", $value)) : $value;
                }

                $emoji->date_modified = Carbon::now()->toDateTimeString();

                if ($emoji->save()) {
                    $response = $response->withStatus(200);
                    $message = json_encode([
                        "message" => "Emoji updated succesfully."
                    ]);
                } else {
                    $response = $response->withStatus(304);
                    $message = json_encode([
                        "message" => "Error updating emoji."
                    ]);
                }
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

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
    public static function patchEmoji($request, $response, $args)
    {
        try {
            $emoji = self::find($args['id']);

            foreach ($request->getParsedBody() as $key => $value) {
                $emoji->$key = $key === "keywords" ? json_encode(explode(",", $value)) : $value;
            }

            $emoji->date_modified = Carbon::now()->toDateTimeString();

            if ($emoji->save()) {
                $response = $response->withStatus(200);
                $message = json_encode([
                    "message" => "Emoji updated succesfully"
                ]);
            } else {
                $response = $response->withStatus(304);
                $message = json_encode([
                    "message" => "Error updating emoji"
                ]);
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

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
    public static function deleteEmoji($request, $response, $args)
    {
        $data = $request->getParsedBody();
        try {
            if (self::destroy($args['id'])) {
                $response = $response->withStatus(200);
                $message = json_encode([
                    "message" => "Emoji deleted succesfully."
                ]);
            } else {
                $response = $response->withStatus(400);
                $message = json_encode([
                    "message" => "Error deleting emoji."
                ]);
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => $e->getMessage()
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }
}
