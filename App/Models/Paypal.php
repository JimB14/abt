<?php

namespace App\Models;

use PDO;
use \App\Config;
use \App\Models\Payflow;

/**
 * Paypal model
 */
class Paypal extends \Core\Model
{
    public static function processPayment($user_id)
    {
        // echo "Connected to static function processPayment() in PayPal model!<br><br>";

        // get tomorrow's date and format for PP for recurring billing commencement date
        $datetime = new \DateTime('tomorrow'); // DateTime in root (no namespace), needs preceding backslash
        $tomorrow = $datetime->format('mdY');

        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // create new instance of Payflow object
        $payflow = new Payflow();

        // test
        // if(is_object($payflow)) {echo '$payflow is an object';} else {echo "False";};
        // exit();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // retrieve post data from form, sanitize & store in variables
        $FIRSTNAME  = (isset($_POST['first_name'])) ? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $LASTNAME   = (isset($_POST['last_name'])) ? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $CARDTYPE   = (isset($_POST['cardtype'])) ? filter_var($_POST['cardtype'], FILTER_SANITIZE_STRING) : '';
        $AMT        = (isset($_POST['amt'])) ? number_format($_POST['amt'], 2) : '';
        $ACCT       = (isset($_POST['acct'])) ? filter_var($_POST['acct'], FILTER_SANITIZE_STRING) : '';
        $exp_month  = (isset($_POST['exp_month'])) ? filter_var($_POST['exp_month'], FILTER_SANITIZE_STRING) : '';
        $exp_year   = (isset($_POST['exp_year'])) ? filter_var($_POST['exp_year'], FILTER_SANITIZE_STRING) : '';
        $EXPDATE    = $exp_month.$exp_year;
        $CVV2       = (isset($_POST['cvv2'])) ? filter_var($_POST['cvv2'], FILTER_SANITIZE_STRING) : '';
        $agree      = (isset($_POST['agree'])) ? filter_var($_POST['agree'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // echo $FIRSTNAME.'<br>';
        // echo $LASTNAME.'<br>';
        // echo $CARDTYPE.'<br>';
        // echo $ACCT.'<br>';
        // echo $EXPDATE.'<br>';
        // echo $CVV2.'<br>';
        // echo $AMT.'<br>';
        // echo $agree.'<br>';
        // exit();

        // check for empty fields - backup for JavaScript failure
        if( ($FIRSTNAME == '') || ($LASTNAME == '') || ($CARDTYPE == '') || ($AMT == '')
            || ($ACCT == '') || ($EXPDATE == '') || ($CVV2 == '') || ($agree != 'on') )
        {
            $payflow->set_errors("All fields required. Please login and try again.");
            exit();
        }

        // extra parameters to pass to PP
        $data_array = [
            'TRXTYPE'         => 'R',
            'TENDER'          => 'C',
            'ACTION'          => 'A',
            'PROFILENAME'     => $FIRSTNAME.$LASTNAME.$user_id,
            'START'           => $tomorrow,
            'PAYPERIOD'       => 'MONT',
            'TERM'            => '0',
            'OPTIONALTRX'     => 'S',
            'OPTIONALTRXAMT'  => '9.95',
            'FIRSTNAME'       => $FIRSTNAME,
            'LASTNAME'        => $LASTNAME,
            'CVV2'            => $CVV2, // for cvv validation response
            'COMMENT1'        => 'Subscription',
            'RETRYNUMDAYS'    => '3',  // The number of consecutive days that PayPal should attempt to process a failed transaction until Approved status is received; maximum value is 4.
            'clientip'        => '0.0.0.0'
            ];

        // test
        //  echo 'VENDOR: ' . $vendor . '<br>';
        //  echo 'USER: ' . $user . '<br>';
        //  echo 'PARTNER: ' . $partner . '<br>';
        //  echo 'PWD: ' . $password . '<br>';
        // echo 'EXPDATE: ' . $EXPDATE . '<br><br>';
        // echo 'POST array<br>';
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // echo '<br><br>';
        // // exit();
        //
        // echo 'Data array<br>';
        // echo '<pre>';
        // print_r($data_array);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();

        // call sale_transaction() of Payflow object & store results in $response
        $response = $payflow->sale_transaction($vendor, $user, $partner, $password, $ACCT, $EXPDATE, $AMT, $CURRENCY='USD', $data_array);


        if (!$payflow->get_errors())
        {
            // test
            // echo 'Response array<br>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // return to Subscribe Controller
            return $response;
        }
        else
        {
            echo $payflow->get_errors();
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
     * cancels recurring billing
     *
     * @param  integer $user_id        The user's ID
     * @param  string  $origprofileid  The paypal profileID stored by PP
     * @return string                  The PP response string
     */
    public static function cancelPayment($user_id, $origprofileid)
    {
        // resource:  https://developer.paypal.com/docs/classic/payflow/recurring-billing/#using-the-cancel-action

        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // store required parameters for Cancel Action in variables
        $trxtype = 'R';
        $tender  = 'C';
        $action  = 'C';
        // origprofileid passed to function above

        // create new instance of Payflow object
        $payflow = new Payflow();

        // test
        // if(is_object($payflow)) {echo '$payflow is an object';} else {echo "False";};
        // exit();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // call cancel_payment() of Payflow object & store results in $respnse
        $response = $payflow->cancelPayment($vendor, $user, $partner, $password, $trxtype, $tender, $action, $origprofileid);


        if (!$payflow->get_errors())
        {
            // test
            // echo 'From Paypal model: Response array<br>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // return to Subscribe Controller
            return $response;
        }
        else
        {
            echo $payflow->get_errors();
        }




    }

}
