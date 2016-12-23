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

        // create new instance of Payflow object & pass parameters from Config class
        $payflow = new Payflow();

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

        // call sale_transaction method of Payflow object & store results in $result
        $response = $payflow->sale_transaction($vendor, $user, $partner, $password, $ACCT, $EXPDATE, $AMT, $CURRENCY='USD', $data_array);


        if (!$payflow->get_errors())
        {
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



    public static function addTransactionData($user_id, $data_array)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // insert paypal transaction data into paypal_log
            $sql = "INSERT INTO paypal_log SET
                    user_id       = :user_id,
                    TRXPNREF      = :TRXPNREF,
                    PPREF         = :PPREF,
                    PROFILEID     = :PROFILEID,
                    CORRELATIONID = :CORRELATIONID,
                    TRANSTIME     = :TRANSTIME,
                    AMT           = :AMT";
            $parameters = [
                ':user_id'        => $user_id,
                ':TRXPNREF'       => $data_array['TRXPNREF'],
                ':PPREF'          => $data_array['PPREF'],
                ':PROFILEID'      => $data_array['PROFILEID'],
                ':CORRELATIONID'  => $data_array['CORRELATIONID'],
                ':TRANSTIME'      => $data_array['TRANSTIME'],
                ':AMT'            => $data_array['AMT']
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

            $results = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return results to Admin/Brokers Controlller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
