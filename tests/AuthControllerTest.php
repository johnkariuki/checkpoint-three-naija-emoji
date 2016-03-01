<?php

namespace NaijaEmoji\Tests;

use Potato\Database\DatabaseConnection;
use NaijaEmoji\Manager\AuthController;
use NaijaEmoji\Manager\EmojiManagerController;
use PHPUnit_Framework_TestCase;
use GuzzleHttp\Client;
use Faker\Factory;

class AuthControllerTest extends PHPUnit_Framework_TestCase
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
        self::createEmojisTable();
        self::createUsersTable();

        self::$faker = Factory::create();
        self::$data['username'] = self::$faker->userName;

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
     * Assert that a user is registered succesfully in the database.
     *
     * assert that a 201 Create statuscode and success message are
     * returned.
     *
     * Assert that record with that username is in the database.
     *
     * @return void
     */
    public function testRegisterUser()
    {
        $user = [
            'username' => self::$data['username'],
            'password' => '123456'
        ];

        $response = self::$client->post('/auth/register', [
            'form_params' => $user
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
        $this->assertEquals('{"message":"User successfully registered."}', $response->getBody());
        $this->assertEquals(1, count(AuthController::findRecord([
            'username' => self::$data['username']
        ])));

    }

    /**
     * Assert that a username that is already registered will throw
     * and excception.
     *
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testNoDuplicateUser()
    {
        $user = [
            'username' => self::$data['username'],
            'password' => '123456'
        ];

        $response = self::$client->post('/auth/register', [
            'form_params' => $user
        ]);
    }

    /**
     * Assert that an unregistered user will throw an exception
     * on login attempt.
     *
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testUnRegisteredUser()
    {
        $badUser = [
            'username' => "johndoeslimguy",
            'password' => '123456'
        ];
        $response = self::$client->post('/auth/login', [
            'form_params' => $badUser
        ]);
    }

    /**
     * assert that testAuthRegisteredUser authenticates a user
     * and returns a success message and a token.
     *
     * Assert that a 200 status code is given and that the
     * user is in the database
     *
     * @return void
     */
    public function testAuthRegisteredUser()
    {
         $awesomeUser = [
            'username' => self::$data['username'],
            'password' => '123456'
         ];

         $response = self::$client->post('/auth/login', [
            'form_params' => $awesomeUser
         ]);

         $this->assertEquals(200, $response->getStatusCode());
         $this->assertEquals('application/json', $response->getHeaderLine('content-type'));

         $this->assertEquals('login successful', json_decode($response->getBody())->message);

         self::$data['token'] = json_decode($response->getBody())->token;
         $this->assertEquals('string', gettype(self::$data['token']));

         $this->assertEquals(1, count(AuthController::findRecord([
            'username' => self::$data['username']
            ])));
    }

    /**
     * Test a logout attemtpt without an emoji API token
     *
     *  @expectedException GuzzleHttp\Exception\ClientException
     *
     * @return void
     */
    public function testunAuthlogout()
    {
        $response = self::$client->get('/auth/logout');
    }

    /**
     * Assert that logout with a valid emoji API token
     * returns JSON with status code 200.
     *
     * Assert that succesfully logout message is returned.
     *
     * @return void
     */
    public function testAuthLogout()
    {
         $response = self::$client->get('/auth/logout', [
            'headers' => [
                'token' => self::$data['token']
            ]
         ]);

         $this->assertEquals(200, $response->getStatusCode());
         $this->assertEquals('application/json', $response->getHeaderLine('content-type'));
         $this->assertEquals('successfully logged out.', json_decode($response->getBody())->message);
    }

    /**
     * Create the emojis table.
     *
     * @return void
     */
    public static function createEmojisTable()
    {
        $sqlQuery = 'CREATE TABLE IF NOT EXISTS `emojis` (
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
     * DROP the emojis and users table.
     *
     * close PDO Database connection.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::$connection->query('DELETE FROM users WHERE id != 1');
    }
}
