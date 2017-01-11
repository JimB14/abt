<?php

namespace App\Models;

use PDO;

/**
 * Paypallog model
 */
class Paypallog extends \Core\Model
{
    /**
     * retrieves Paypal's profile id store in table
     *
     * @param  Integer  $user_id  The user ID
     * @return String             The profile ID
     */
    public static function getPaypalData($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM paypal_log
                    WHERE user_id = :user_id";
            $parameters = [
              ':user_id' => $user_id
            ];
            $stmt = $db->prepare($sql);
            $stmt->execute($parameters);

            $profile = $stmt->fetch(PDO::FETCH_OBJ);

            // return object
            return $profile;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * adds transaction data to paypal_log
     *
     * @param integer $user_id    The user's ID
     * @param array   $data_array Required and addtional data for PP
     * @return boolean
     */
    public static function addTransactionData($user_id, $data_array)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // insert paypal transaction data into paypal_log
            $sql = "INSERT INTO paypal_log SET
                    user_id       = :user_id,
                    RESULT        = :RESULT,
                    RESPMSG       = :RESPMSG,
                    TRXPNREF      = :TRXPNREF,
                    PPREF         = :PPREF,
                    PROFILEID     = :PROFILEID,
                    CORRELATIONID = :CORRELATIONID,
                    TRANSTIME     = :TRANSTIME,
                    AMT           = :AMT,
                    RPREF         = :RPREF";
            $parameters = [
                ':user_id'        => $user_id,
                ':RESULT'         => $data_array['RESULT'],
                ':RESPMSG'        => $data_array['RESPMSG'],
                ':TRXPNREF'       => $data_array['TRXPNREF'],
                ':PPREF'          => $data_array['PPREF'],
                ':PROFILEID'      => $data_array['PROFILEID'],
                ':CORRELATIONID'  => $data_array['CORRELATIONID'],
                ':TRANSTIME'      => $data_array['TRANSTIME'],
                ':AMT'            => $data_array['AMT'],
                ':RPREF'          => $data_array['RPREF']
            ];
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($parameters);

            // return boolean to Paypal Controller
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * adds transaction data to paypal_log
     *
     * @param integer $user_id    The user's ID
     * @param array   $data_array Required and addtional data for PP
     * @return boolean
     */
    public static function addTransactionDataFromModification($user_id, $data_array)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // insert paypal transaction data into paypal_log
            $sql = "INSERT INTO paypal_log SET
                    user_id       = :user_id,
                    RESULT        = :RESULT,
                    RESPMSG       = :RESPMSG,
                    PROFILEID     = :PROFILEID,
                    RPREF         = :RPREF,
                    AMT           = :AMT";
            $parameters = [
                ':user_id'        => $user_id,
                ':RESULT'         => $data_array['RESULT'],
                ':RESPMSG'        => $data_array['RESPMSG'],
                ':PROFILEID'      => $data_array['PROFILEID'],
                ':RPREF'          => $data_array['RPREF'],
                ':AMT'            => $data_array['AMT']
            ];
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($parameters);

            // return boolean to Subscribe Controller
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * gets transaction data from paypal_log
     *
     * @param  int $user->id    The user ID
     * @return object           The transaction records
     */
    public static function getTransactionData($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM paypal_log
                    WHERE user_id = :user_id
                    ORDER BY TRANSTIME DESC";
            $parameters = [
                ':user_id'  => $user_id
            ];
            $stmt = $db->prepare($sql);
            $stmt->execute($parameters);

            // store record in variable as object
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return results (object) to Admin/Brokers Controlller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * adds cancel transaction data to paypal_log
     *
     * @param integer $user_id      The user's ID
     * @param array   $data_array   The PayPal transaction respose data
     * @return boolean
     */
    public static function addCancelTransactionData($user_id, $data_array)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // insert paypal transaction data into paypal_log
            $sql = "INSERT INTO paypal_log SET
                    user_id   = :user_id,
                    RESULT    = :RESULT,
                    RPREF     = :RPREF,
                    PROFILEID = :PROFILEID,
                    RESPMSG   = :RESPMSG";
            $parameters = [
                ':user_id'    => $user_id,
                ':RESULT'     => $data_array['RESULT'],
                ':RPREF'      => $data_array['RPREF'],
                ':PROFILEID'  => $data_array['PROFILEID'],
                ':RESPMSG'    => $data_array['RESPMSG']
            ];
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($parameters);

            // return boolean to Subscribe Controller
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }
}
