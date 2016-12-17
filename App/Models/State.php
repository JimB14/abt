<?php

namespace App\Models;

use PDO;

/**
 * State model
 */
class State extends \Core\Model
{
    public static function getStates()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM states";
            $stmt = $db->query($sql);
            $states = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $states;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
}
