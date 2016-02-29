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
     * Run the createUsersTable method.
     * Run the createEmojisTable method.
     *
     * Create a faker object and add a username
     * to the data array.
     *
     *  create the guzzleHTTP client object.
     */
    public static function setUpBeforeClass()
    {
        self::$connection = DatabaseConnection::connect();
        self::createUsersTable();
        self::createEmojisTable();

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
