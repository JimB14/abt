<?php

namespace Core;

use PDO;
use App\Config; // use Config class without full namespace

/**
 * Base Model
 *
 * PHP version 7.0
 *
 */
abstract class Model
{
    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
    protected static function getDB()
    {
        // static variable value is remembered between calls to the method
        // connection made once and reused after the 1st connection
        static $db = null; // cache db connection by using static variable; brilliant!

        if($db === null)
        {
            try
            {
                $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' .
                      Config::DB_NAME . ';charset=utf8';
                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

                // throw an Exception when a db error occurs
                // http://php.net/manual/en/pdo.setattribute.php
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (PDOException $e)
            {
                echo $e->getMessage();
            }
        }
        return $db;
    }
}
