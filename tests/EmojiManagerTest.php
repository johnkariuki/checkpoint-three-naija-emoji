<?php

namespace NaijaEmoji\Tests;

use Potato\Database\DatabaseConnection;
use NaijaEmoji\Manager\AuthController;
use NaijaEmoji\Manager\EmojiManagerController;
use PHPUnit_Framework_TestCase;
use GuzzleHttp\Client;
use Faker\Factory;

class EmojiManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Contains a PDO connection object returned by
     * Database connection class.
     *
     * @var object
     */
    protected static $connection;

    /**
     * Root URI of the naija emoji API.
     *
     * @var string
     */
    protected static $url = 'https://naijaemoji-staging.herokuapp.com';

    /**
     * Holds A Faker/Generator Instance.
     *
     * @var Object
     */
    protected static $faker;

    /**
     * Array of al data needed between one or more
     * test methods.
     *
     * @var array
     */
    protected static $data = [];

    /**
     * GuzzleHTTP client object.
     *
     * @var Object
     */
    protected static $client;

    /**
     * Create persitent database connection to remain
     * until all tests run.
     *
     * Create a faker object and add a username
     * to the data array.
     *
     *  create the guzzleHTTP client object.
     */
    public static function setUpBeforeClass()
    {
        self::$connection = DatabaseConnection::connect();
        //self::createEmojisTable();
        //self::createUsersTable();

        self::$faker = Factory::create();

        self::$data['username'] = self::$faker->userName;

        self::$data['newEmoji'] = [
            'name' => 'innocent',
            'char' => '😇 ',
            'keywords' => 'happy, holy, angel, sweet, awww, innocent',
            'category' => 'person'
        ];

        self::$client = new Client([
              'base_uri' => self::$url
        ]);
    }

    /**
     * Assert that GET / does the following:
     *
     * return a status code of 200
     *
     * return JSON data format
     *
     * return the welcome message
     *
     * @return void
     */
    public function testEmojiApiRoot()
    {
        $response = self::$client->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals('{"message":"welcome to the naija-emoji RESTful Api"}', $response->getBody());
    }
    /**
     *Assert that a creating a new emoji while unauthenticated
     * throws an error.(returns 400 bad request)
     *
     * @expectedException GuzzleHttp\Exception\ClientException
     *
     * @return void
     */
    public function testUnAuthAddEmoji()
    {
        $response = self::$client->post('/emojis', [
            'form_params' => self::$data['newEmoji']
        ]);
    }

    /**
     * Assert that trying to post an emoji with incomplete details
     * throws an exception
     *
     * @expectedException GuzzleHttp\Exception\ClientException
     *
     * @return void
     */
    public function testRequiredEmojiFields()
    {
        $userName = self::$faker->userName;
        $user = [
            'username' => $userName,
            'password' => '123456'
        ];

        self::$client->post('/auth/register', [
            'form_params' => $user
        ]);

        $response = self::$client->post('/auth/login', [
            'form_params' => $user
         ]);

        $token = json_decode($response->getBody())->token;

        $response = self::$client->post('/emojis', [
            'headers' => [
                'token' => $token
            ],
            'form_params' => [
                'name' => 'awesomeIncompleteEmoji'
            ]
        ]);
    }

    /**
     * Assert that a registered and authenticated user can add
     * a new emoji
     *
     * @return void
     */
    public function testAuthAddEmoji()
    {
        $userName = self::$faker->userName;
        $user = [
            'username' => $userName,
            'password' => '123456'
        ];

        self::$client->post('/auth/register', [
            'form_params' => $user
        ]);

        $response = self::$client->post('/auth/login', [
            'form_params' => $user
         ]);

        $token = json_decode($response->getBody())->token;

        //no of emojis
        $emojis = count(EmojiManagerController::findRecord([
            'name' => 'innocent'
        ]));

        $response = self::$client->post('/emojis', [
            'headers' => [
                'token' => $token
            ],
            'form_params' => self::$data['newEmoji']
        ]);

         $this->assertEquals(201, $response->getStatusCode());
         $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
         $this->assertEquals('Emoji added succesfully.', json_decode($response->getBody())->message);

         $this->assertEquals($emojis++, count(EmojiManagerController::findRecord([
            'name' => 'innocent'
         ])));
    }

    /**
     * assert that GET /emojis route returns all emojis in the database as
     * a JSON object.
     *
     * Assert that it has the emoji fields.
     *
     * @return [type] [description]
     */
    public function testGetAllEmojis()
    {
        $response = self::$client->get('/emojis');

         $this->assertEquals(200, $response->getStatusCode());
         $this->assertEquals('application/json', $response->getHeaderLine('content-type'));

         $emojis = json_decode($response->getBody(), true);
         $this->assertTrue(is_array($emojis));

         self::$data['id'] = $emojis[0]['id'];

         $this->assertArrayHasKey('name', $emojis[0]);
         $this->assertArrayHasKey('char', $emojis[0]);
         $this->assertArrayHasKey('date_created', $emojis[0]);
    }

    /**
     * Assert that GET /emojis{id} returns a JSON object that holds one
     * emoji.
     *
     * @return void
     */
    public function testgetOneEmoji()
    {
        $response = self::$client->get('/emojis/' . self::$data['id']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));

        $emojis = json_decode($response->getBody(), true);
        $this->assertTrue(is_array($emojis));

        self::$data['id'] = $emojis['id'];

        $this->assertArrayHasKey('name', $emojis);
        $this->assertArrayHasKey('char', $emojis);
        $this->assertArrayHasKey('date_created', $emojis);
    }

    /**
     * Assert that error is thrown when a non existent Emoji
     * is searched.
     *
     * @expectedException GuzzleHttp\Exception\ClientException
     *
     * @return void
     */
    public function testGetNonExistentEmoji()
    {
        self::$client->get('/emojis/314210001');
    }

    /**
     * Asert that GET /emojis{field}{name} returns  JSON object with all emojis
     * that meet a certain criteria.
     *
     * @return void
     */
    public function testGetEmojiByField()
    {
        $response = self::$client->get('/emojis/category/person');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));


        $emojis = json_decode($response->getBody(), true);
        $this->assertTrue(is_array($emojis));
        $this->assertArrayHasKey('name', $emojis[0]);
        $this->assertArrayHasKey('char', $emojis[0]);
        $this->assertArrayHasKey('date_created', $emojis[0]);
    }

    /**
     * Assert that PUT /emojis/{id} updates an exisiting emoji.
     *
     * Asserr that PATCH /emojis/{id} updates specifid fields of an emoji.
     *
     * @return void
     */
    public function testPutRoute()
    {
        $userName = self::$faker->userName;
        $user = [
            'username' => $userName,
            'password' => '123456'
        ];

        self::$client->post('/auth/register', [
            'form_params' => $user
        ]);

        $response = self::$client->post('/auth/login', [
            'form_params' => $user
         ]);

        $token = json_decode($response->getBody())->token;

        $response = self::$client->put('/emojis/'. self::$data['id'], [
            'headers' => [
                'token' => $token
            ],
            'form_params' => [
                'name' => 'arms',
                'char' => '💪',
                'keywords' => 'strong, arm, weider',
                'category' => 'arms'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals('Emoji updated succesfully.', json_decode($response->getBody())->message);

        $response = self::$client->patch('/emojis/'. self::$data['id'], [
            'headers' => [
                'token' => $token
            ],
            'form_params' => [
                'name' => 'big arms'
            ]
         ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals('Emoji updated succesfully', json_decode($response->getBody())->message);
    }

    /**
     * Assert that DELETE /emojis/{id} removes an exisiting emoji
     *
     * @return void
     */
    public function testDeleteEmoji()
    {
        $userName = self::$faker->userName;
        $user = [
            'username' => $userName,
            'password' => '123456'
        ];

        self::$client->post('/auth/register', [
            'form_params' => $user
        ]);

        $response = self::$client->post('/auth/login', [
            'form_params' => $user
         ]);

        $token = json_decode($response->getBody())->token;
        $response = self::$client->delete('/emojis/'. self::$data['id'], [
            'headers' => [
                'token' => $token
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals('Emoji deleted succesfully.', json_decode($response->getBody())->message);
        $this->assertFalse(EmojiManagerController::findRecord(self::$data['id']));
    }

    /**
     * Create the emojis table.
     *
     * @return void
     */
    public static function createEmojisTable()
    {
        $sqlQuery = 'CREATE TABLE IF NOT EXISTS "emojis" (
                    `id`    INTEGER PRIMARY KEY AUTOINCREMENT,
                    `name`  TEXT,
                    `char`  TEXT,
                    `keywords`  TEXT,
                    `category`  TEXT,
                    `date_created`  TEXT,
                    `date_modified` TEXT,
                    `created_by`    TEXT
                )';

        self::$connection->exec($sqlQuery);
    }

    /**
     * Create the users table.
     *
     * @return void
     */
    public static function createUsersTable()
    {
        $sqlQuery = 'CREATE TABLE IF NOT EXISTS "users" (
                `id`    INTEGER PRIMARY KEY AUTOINCREMENT,
                `username`  TEXT,
                `password`  TEXT,
                `token` TEXT,
                `expires`   TEXT
            )';

        self::$connection->exec($sqlQuery);
    }

    /**
     *
     * close PDO Database connection.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::$connection = null;
    }
}
