<?php

namespace App\Models;

use PDO;

/**
 * Paypallog model
 */
class Paypallog extends \Core\Model
{
    /**
     * retrieves Paypal's profile id stored in `paypal_log` table
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
                    WHERE user_id = :user_id
                    LIMIT 1";
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
    public static function addNewSubscriberWithFreeTrialTransactionData($user_id, $data_array)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // insert paypal transaction data into paypal_log
            $sql = "INSERT INTO paypal_log SET
                    user_id       = :user_id,
                    RESULT        = :RESULT,
                    PROFILEID     = :PROFILEID,
                    RESPMSG       = :RESPMSG,
                    TRXRESULT     = :TRXRESULT,
                    TRXPNREF      = :TRXPNREF,
                    TRXRESPMSG    = :TRXRESPMSG,
                    AUTHCODE      = :AUTHCODE,
                    CVV2MATCH     = :CVV2MATCH,
                    CORRELATIONID = :CORRELATIONID,
                    PPREF         = :PPREF,
                    PROCCVV2      = :PROCCVV2,
                    TRANSTIME     = :TRANSTIME,
                    FIRSTNAME     = :FIRSTNAME,
                    LASTNAME      = :LASTNAME,
                    AMT           = :AMT,
                    ACCT          = :ACCT,
                    EXPDATE       = :EXPDATE,
                    CARDTYPE      = :CARDTYPE";
            $parameters = [
                ':user_id'        => $user_id,
                ':RESULT'         => $data_array['RESULT'],
                ':PROFILEID'      => $data_array['PROFILEID'],
                ':RESPMSG'        => $data_array['RESPMSG'],
                ':TRXRESULT'      => $data_array['TRXRESULT'],
                ':TRXPNREF'       => $data_array['TRXPNREF'],
                ':TRXRESPMSG'     => $data_array['TRXRESPMSG'],
                ':AUTHCODE'       => $data_array['AUTHCODE'],
                ':PPREF'          => $data_array['PPREF'],
                ':CVV2MATCH'      => $data_array['CVV2MATCH'],
                ':CORRELATIONID'  => $data_array['CORRELATIONID'],
                ':PROCCVV2'       => $data_array['PROCCVV2'],
                ':TRANSTIME'      => $data_array['TRANSTIME'],
                ':FIRSTNAME'      => $data_array['FIRSTNAME'],
                ':AMT'            => $data_array['AMT'],
                ':LASTNAME'       => $data_array['LASTNAME'],
                ':ACCT'           => $data_array['ACCT'],
                ':EXPDATE'        => $data_array['EXPDATE'],
                ':CARDTYPE'       => $data_array['CARDTYPE']
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
     * adds credit card update transaction data to `paypal_log` table
     *
     * @param integer $user_id      The user's ID
     * @param array   $data_array   Required and addtional data for PP
     * @return boolean
     */
    public static function addTransactionDataForCreditCardUpdate($user_id, $data_array)
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
                    RPREF         = :RPREF";
            $parameters = [
                ':user_id'        => $user_id,
                ':RESULT'         => $data_array['RESULT'],
                ':RESPMSG'        => $data_array['RESPMSG'],
                ':PROFILEID'      => $data_array['PROFILEID'],
                ':RPREF'          => $data_array['RPREF']
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
                    LIMIT 1";
            $parameters = [
                ':user_id'  => $user_id
            ];
            $stmt = $db->prepare($sql);
            $stmt->execute($parameters);

            // store record in variable as object
            $results = $stmt->fetch(PDO::FETCH_OBJ);

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
