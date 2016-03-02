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

            $message = json_encode(self::prettifyArray($emojis, true));

        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => "Error processing request."
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
                $message = json_encode(self::prettifyArray($emoji, false));
            } else {
                $response = $response->withStatus(400);
                $message = json_encode([
                    'message' => "no emoji found"
                ]);
            }


        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => "Error processing request."
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
        $required = ['name', 'char', 'keywords', 'category'];
        try {
            if (is_array($data) && count(array_diff($required, array_keys($data)))) {
                throw new PDOException("Missing some required fields");
            } else {

                if (! self::testEmptyElements($data, $required)) {

                    $response = $response->withStatus(400);
                    $message = json_encode([
                        "message" => "Empty values provided."
                    ]);
                } else {
                    $emoji = new self();

                    $emoji->name = $data["name"];
                    $emoji->char = $data["char"];
                    $emoji->keywords = json_encode(explode(",", $data["keywords"]));
                    $emoji->category = $data["category"];
                    $emoji->date_created = Carbon::now()->toDateTimeString();
                    $emoji->date_modified = Carbon::now()->toDateTimeString();
                    $emoji->created_by = AuthController::findRecord([
                            "token" => $request->getHeader('HTTP_TOKEN')[0]
                        ])["username"];

                    if ($emoji->save()) {
                        $response = $response->withStatus(201);
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
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => "Error processing request."
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
            $required = ['name', 'char', 'keywords', 'category'];

            if (count(array_diff($required, array_keys($request->getParsedBody())))) {
                throw new PDOException("Missing some required fields");
            } else {
                if (! self::testEmptyElements($request->getParsedBody(), $required)) {

                    $response = $response->withStatus(400);
                    $message = json_encode([
                        "message" => "Empty values provided."
                    ]);
                } else {
                    foreach ($request->getParsedBody() as $key => $value) {
                        $emoji->$key = $key === "keywords" ? json_encode(explode(",", $value)) : $value;
                    }

                    $emoji->date_modified = Carbon::now()->toDateTimeString();

                    if ($emoji->save()) {
                        $response = $response->withStatus(201);
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
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => "Error processing request."
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
            $required = ['name', 'char', 'keywords', 'category'];

            if (! self::testEmptyElements($request->getParsedBody(), $required)) {
                    $response = $response->withStatus(400);
                    $message = json_encode([
                        "message" => "Empty values provided."
                    ]);
            } else {
                foreach ($request->getParsedBody() as $key => $value) {
                    $emoji->$key = $key === "keywords" ? json_encode(explode(",", $value)) : $value;
                }

                $emoji->date_modified = Carbon::now()->toDateTimeString();

                if ($emoji->save()) {
                    $response = $response->withStatus(201);
                    $message = json_encode([
                        "message" => "Emoji updated succesfully"
                    ]);
                } else {
                    $response = $response->withStatus(304);
                    $message = json_encode([
                        "message" => "Error updating emoji"
                    ]);
                }
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                "message" => "Error processing request."
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
                "message" => "Error processing request."
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
    }

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
    public static function searchCategory($request, $response, $args)
    {
        try {
            $emojis = EmojiManagerController::findRecords([
                    $args['field'] => $args['name']
                ]);

            if ($emojis) {
                $response = $response->withStatus(200);
                $message = json_encode($emojis);
            } else {
                $response = $response->withStatus(400);
                $message = json_encode([
                    'message' => "no emojis found whose {$args['field']} field is  {$args['name']}"
                ]);
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(400);
            $message = json_encode([
                'message' => "Error processing request."
            ]);
        }

        $response = $response->withHeader('Content-type', 'application/json');
        return $response->write($message);
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

    /**
     * Escape returned keywords array
     * @param  array   $emoji array of emoji data
     * @param  boolean $multi multi or single arrray
     * @return array         array of emoji data
     */
    public static function prettifyArray(array $emoji, $multi = false)
    {
        if ($multi) {
            foreach ($emoji as $emojiKey => $singlEmoji) {
                foreach ($singlEmoji as $key => $value) {
                    if ($key === 'keywords') {
                        $emoji[$emojiKey][$key] = str_replace('"', "'", $singlEmoji[$key]);
                    }
                }
            }
        } else {
            foreach ($emoji as $key => $value) {
                if ($key === 'keywords') {
                    $emoji[$key] = str_replace('"', "'", $emoji[$key]);
                    break;
                }
            }
        }
        return $emoji;
    }
}
