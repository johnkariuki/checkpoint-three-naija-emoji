<?php

namespace NaijaEmoji\Tests;

use Potato\Database\DatabaseConnection;
use PHPUnit_Framework_TestCase;
use GuzzleHttp\Client;

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
     *  create the guzzleHTTP client object.
     */
    public static function setUpBeforeClass()
    {
        self::$connection = DatabaseConnection::connect();
        self::createUsersTable();
        self::createEmojisTable();

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
     * Create the emojis table.
     *
     * @return void
     */
    public static function createEmojisTable()
    {
        self::$connection = DatabaseConnection::connect();

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
        self::$connection = DatabaseConnection::connect();

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
        self::$connection->exec('DROP TABLE IF EXISTS emojis');
        self::$connection->exec('DROP TABLE IF EXISTS users');

        self::$connection = null;
    }
}
