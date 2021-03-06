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
  /**
   * process payment via PayPal's payflow gateway
   *
   * @param  Integer  $user_id  The users ID
   * @return String             Key/Value pairs from PayPal
   */
  public static function processPaymentWithFreeTrial($user_id)
  {
      // echo "Connected to static function processPaymentWithFreeTrial() in PayPal model!<br><br>";

      // get tomorrow's date and format for PP for recurring billing commencement date
      // $datetime = new \DateTime('tomorrow'); // DateTime in root (no namespace), needs preceding backslash
      // $tomorrow = $datetime->format('mdY');

      // create date for recurring billing & format for PayPal
      $tomorrow = new \DateTime('tomorrow');
      $billDate = $tomorrow->modify('+1 month');
      $billDate = $billDate->format('mdY');  // for PayPal

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
      // value of $AMT collected but not used; using value constant SUBSCRIPTION in Config.php
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
          'AMT'             => Config::SUBSCRIPTION,  // from Config.php not form
          'PROFILENAME'     => $FIRSTNAME.$LASTNAME.$user_id,
          'START'           => $billDate,
          'PAYPERIOD'       => 'MONT',
          'TERM'            => '0',
          'OPTIONALTRX'     => 'A',
          // 'OPTIONALTRX'     => 'S',
          // 'OPTIONALTRXAMT'  => Config::SUBSCRIPTION,
          'FIRSTNAME'       => $FIRSTNAME,
          'LASTNAME'        => $LASTNAME,
          'CVV2'            => $CVV2, // for cvv validation response
          'COMMENT1'        => 'Subscription with one month free trial',
          'RETRYNUMDAYS'    => '3',  // The number of consecutive days that PayPal should attempt to process a failed transaction until Approved status is received; maximum value is 4.
          'IPADDRESS'       => $_SERVER['REMOTE_ADDR']
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
      $response = $payflow->sale_transaction_with_free_trial($vendor, $user, $partner, $password, $ACCT, $EXPDATE, $CURRENCY='USD', $data_array);


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
     * process payment via PayPal's payflow gateway
     *
     * @param  Integer  $user_id  The users ID
     * @return String             Key/Value pairs from PayPal
     */
    public static function processPayment($user_id)
    {
        // echo "Connected to static function processPayment() in PayPal model!<br><br>";

        // get tomorrow's date and format for PP for recurring billing commencement date
        // $datetime = new \DateTime('tomorrow'); // DateTime in root (no namespace), needs preceding backslash
        // $tomorrow = $datetime->format('mdY');

        // create date for recurring billing & format for PayPal
        $tomorrow = new \DateTime('tomorrow');
        $billDate = $tomorrow->modify('+1 month');
        $billDate = $billDate->format('mdY');  // for PayPal

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
            'AMT'             => Config::SUBSCRIPTION,
            'PROFILENAME'     => $FIRSTNAME.$LASTNAME.$user_id,
            'START'           => $billDate,
            'PAYPERIOD'       => 'MONT',
            'TERM'            => '0',
            'OPTIONALTRX'     => 'S',
            'OPTIONALTRXAMT'  => Config::SUBSCRIPTION,
            'FIRSTNAME'       => $FIRSTNAME,
            'LASTNAME'        => $LASTNAME,
            'CVV2'            => $CVV2, // for cvv validation response
            'COMMENT1'        => 'Subscription',
            'RETRYNUMDAYS'    => '3',  // The number of consecutive days that PayPal should attempt to process a failed transaction until Approved status is received; maximum value is 4.
            'IPADDRESS'       => $_SERVER['REMOTE_ADDR']
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
        $trxtype = 'R';  // R = recurring
        $tender  = 'C';  // C = credit card
        $action  = 'C';  // C = cancel
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


    /**
     * process payment modification
     *
     * @param  Integer  $user_id    The users ID
     * @param  String   $profileid  The users PayPal profile ID
     * @return String               Key/Value pairs from PayPal
     */
    public static function processPaymentForNewAgents($profileid)
    {
        // test
        // echo "Connected to public static function processPaymentForNewAgents() in PayPal model!<br><br>";
        // echo $user_id . '<br>';
        // echo $profileid . '<br>';
        // echo $agent_count . '<br>';
        // echo $max_agents . '<br>';
        // exit();

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
        $AGENTS = (isset($_POST['agent_qty'])) ? filter_var($_POST['agent_qty'], FILTER_SANITIZE_STRING) : '';
        $AMT    = (isset($_POST['amt'])) ? number_format($_POST['amt'], 2) : '';
        $agree  = (isset($_POST['agree'])) ? filter_var($_POST['agree'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $profileid.'<br>';
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // echo $AGENTS.'<br>';
        // echo $AMT.'<br>';
        // echo $agree.'<br>';
        // exit();

        // check for empty fields - backup for JavaScript failure
        if( ($AMT == '') || ($agree != 'on') )
        {
            $payflow->set_errors("All fields required. Please login and try again.");
            exit();
        }

        // extra parameters to pass to PP
        $data_array = [
            'ORIGPROFILEID' => $profileid,
            'TRXTYPE'       => 'R',  // R = Recurring
            'TENDER'        => 'C',  // C = Credit card
            'ACTION'        => 'M',  // M = Modify
            'AMT'           => $AMT  // new monthly billing amount
        ];

        // test
        // echo 'VENDOR: ' . $vendor . '<br>';
        // echo 'USER: ' . $user . '<br>';
        // echo 'PARTNER: ' . $partner . '<br>';
        // echo 'PWD: ' . $password . '<br>';
        // echo 'POST array<br>';
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();
        //
        // echo 'Data array<br>';
        // echo '<pre>';
        // print_r($data_array);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();

        // call processPaymentForNewAgents() of Payflow object & store results in $response
        $response = $payflow->processPaymentForNewAgents($vendor, $user, $partner, $password, $data_array);


        if (!$payflow->get_errors())
        {
            // test
            // echo 'Response array<br>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // store results in array
            $results = [
              'response'      => $response,  // response from PayPal
              'agents_added'  => $AGENTS,    // number of new agents added
              'new_amount'    => $AMT        // new monthly recurring bill
            ];

            // return to Subscribe Controller
            return $results;
        }
        else
        {
            echo $payflow->get_errors();
        }
    }


    /**
     * process payment reduction
     *
     * @param  Integer    $user_id      The users ID
     * @param  String     $profileid    The users PayPal profile ID
     * @param  Integer    $agent_count  Number of agents to remove
     * @return String                   Key/Value pairs from PayPal
     */
    public static function processPaymentReduction($user_id, $profileid, $new_amt)
    {
        // test
        // echo "Connected to public static function processPaymentReduction() in PayPal Model!<br><br>";
        // echo $user_id . '<br>';
        // echo $profileid . '<br>';
        // echo $new_amt . '<br>';
        // exit();

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

        // extra parameters to pass to PP
        $data_array = [
            'TRXTYPE'         => 'R',         // required   'R' = recurring
            'ACTION'          => 'M',         // required   'M' = modify
            'TENDER'          => 'C',         // required   'C' = credit card
            'ORIGPROFILEID'   => $profileid,  // required   user's PayPal profile ID
            'AMT'             => $new_amt,
            'COMMENT1'        => 'Reduce max agent count',  // optional
            'IPADDRESS'       => $_SERVER['REMOTE_ADDR']    // optional
        ];

        // test
        // echo '<br>Data array<br>';
        // echo '<pre>';
        // print_r($data_array);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();

        // call reduce_bill() of Payflow object & store results in $response
        $response = $payflow->reduceBill($vendor, $user, $partner, $password, $data_array);

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
     * retrieves profile status response
     *
     * @param  String  $profileid   The PayPal PROFILEID
     * @return String               The response string
     */
    public static function profileStatusInquiry($profileid)
    {
        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // create new instance of Payflow object
        $payflow = new Payflow();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // four required parameters to pass to PP (along with four credentials above)
        $data_array = [
            'ORIGPROFILEID' => $profileid,
            'TRXTYPE'       => 'R',  //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'TENDER'        => 'C',  //  C = credit card, P = PayPal
            'ACTION'        => 'I'   //  I = Inquiry
        ];

        // call sale_transaction() of Payflow object & store results in $response
        $inquiryResponse = $payflow->profileStatusInquiry($vendor, $user, $partner, $password, $data_array);

        if (!$payflow->get_errors())
        {
            // test
            // echo 'Response array<br>';
            // echo '<pre>';
            // print_r($response);
            // echo '</pre>';
            // exit();

            // return to Subscribe Controller
            return $inquiryResponse;
        }
        else
        {
            echo $payflow->get_errors();
        }
    }



    /**
     * authorizes new credit card
     *
     * @param  String   $profileid    The use's PayPal PROFILEID
     * @return String                 Need the PNREF
     */
    public static function authorizeCreditCard()
    {
        // retrieve post data from form, sanitize & store in variables
        $FIRSTNAME  = (isset($_POST['first_name'])) ? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $LASTNAME   = (isset($_POST['last_name'])) ? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $CARDTYPE   = (isset($_POST['cardtype'])) ? filter_var($_POST['cardtype'], FILTER_SANITIZE_STRING) : '';
        $ACCT       = (isset($_POST['acct'])) ? filter_var($_POST['acct'], FILTER_SANITIZE_STRING) : '';
        $exp_month  = (isset($_POST['exp_month'])) ? filter_var($_POST['exp_month'], FILTER_SANITIZE_STRING) : '';
        $exp_year   = (isset($_POST['exp_year'])) ? filter_var($_POST['exp_year'], FILTER_SANITIZE_STRING) : '';
        $EXPDATE    = $exp_month.$exp_year;
        $CVV2       = (isset($_POST['cvv2'])) ? filter_var($_POST['cvv2'], FILTER_SANITIZE_STRING) : '';
        $agree      = (isset($_POST['agree'])) ? filter_var($_POST['agree'], FILTER_SANITIZE_STRING) : '';

        // check for empty fields - backup for JavaScript failure
        if( ($FIRSTNAME == '') || ($LASTNAME == '') || ($CARDTYPE == '')
            || ($ACCT == '') || ($EXPDATE == '') || ($CVV2 == '') || ($agree != 'on') )
        {
            $payflow->set_errors("All fields required. Please login and try again.");
            exit();
        }

        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // create new instance of Payflow object
        $payflow = new Payflow();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // four required parameters to pass to PP (along with four credentials above)
        $data_array = [
            'TRXTYPE'       => 'A',   //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'TENDER'        => 'C',   //  C = Credit card
            'FIRSTNAME'     => $FIRSTNAME,
            'LASTNAME'      => $LASTNAME,
            'CARDTYPE'      => $CARDTYPE,
            'ACCT'          => $ACCT,
            'EXPDATE'       => $EXPDATE,
            'CVV2'          => $CVV2,
            'CURRENCY'      => 'USD',
            'AMT'           => 0,
            'IPADDRESS'     => $_SERVER['REMOTE_ADDR']
        ];

        // call paymentInquiry() of Payflow object & store results in $response
        $response = $payflow->authorizeCreditCard($vendor, $user, $partner, $password, $data_array);

        if (!$payflow->get_errors())
        {
            // test
            // echo 'Response array<br>';
            // echo '<pre>';
            // print_r($inquiryResponse);
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
     * updates profileid with new credit card data
     *
     * @param  String   $profileid    PayPal's profile ID
     * @param  String   $pnref        PP's transaction reference from authorization approval
     * @return String                 PP's response
     */
    public static function updateUserProfileWithNewCardData($profileid, $pnref)
    {
        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // create new instance of Payflow object
        $payflow = new Payflow();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // four required parameters to pass to PP (along with four credentials above)
        $data_array = [
            'TRXTYPE'       => 'R',   //  R = Recurring, S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
            'TENDER'        => 'C',   //  C = Credit card
            'ACTION'        => 'M',   //  M = Modify
            'ORIGID'        => $pnref,
            'ORIGPROFILEID' => $profileid
        ];

        // call paymentInquiry() of Payflow object & store results in $response
        $response = $payflow->updateUserProfileWithNewCardData($vendor, $user, $partner, $password, $data_array);

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



    public static function processReactivation($profileid)
    {
        // echo "Connected to processReactivation() in Paypal Model.";

        // retrieve post data from form, sanitize & store in variables
        $FIRSTNAME  = (isset($_POST['first_name'])) ? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $LASTNAME   = (isset($_POST['last_name'])) ? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $CARDTYPE   = (isset($_POST['cardtype'])) ? filter_var($_POST['cardtype'], FILTER_SANITIZE_STRING) : '';
        $ACCT       = (isset($_POST['acct'])) ? filter_var($_POST['acct'], FILTER_SANITIZE_STRING) : '';
        $exp_month  = (isset($_POST['exp_month'])) ? filter_var($_POST['exp_month'], FILTER_SANITIZE_STRING) : '';
        $exp_year   = (isset($_POST['exp_year'])) ? filter_var($_POST['exp_year'], FILTER_SANITIZE_STRING) : '';
        $EXPDATE    = $exp_month.$exp_year;
        $CVV2       = (isset($_POST['cvv2'])) ? filter_var($_POST['cvv2'], FILTER_SANITIZE_STRING) : '';
        $AMT        = (isset($_POST['amt'])) ? number_format($_POST['amt'], 2) : '';
        $agree      = (isset($_POST['agree'])) ? filter_var($_POST['agree'], FILTER_SANITIZE_STRING) : '';

        // check for empty fields - backup for JavaScript failure
        if( ($FIRSTNAME == '') || ($LASTNAME == '') || ($CARDTYPE == '')
            || ($ACCT == '') || ($EXPDATE == '') || ($CVV2 == '') || ($agree != 'on') )
        {
            $payflow->set_errors("All fields required. Please login and try again.");
            exit();
        }

        // store PP credentials in variables
        $vendor   = Config::PAYPAL_VENDOR;
        $user     = Config::PAYPAL_USER;
        $partner  = Config::PAYPAL_PARTNER;
        $password = Config::PAYPAL_PWD;

        // create new instance of Payflow object
        $payflow = new Payflow();

        if ($payflow->get_errors())
        {
            echo $payflow->get_errors();
            exit;
        }

        // get tomorrow's date and format for PP for recurring billing commencement date
        // $datetime = new \DateTime('tomorrow'); // DateTime in root (no namespace), needs preceding backslash
        // $tomorrow = $datetime->format('mdY');

        // create date for recurring billing & format for PayPal
        $tomorrow = new \DateTime('tomorrow');
        $billDate = $tomorrow->modify('+1 month');
        $billDate = $billDate->format('mdY');  // for PayPal

        // four required parameters to pass to PP (along with four credentials above)
        $data_array = [
            'TRXTYPE'       => 'R',   //  R = Recurring
            'TENDER'        => 'C',   //  C = Credit card
            'ACTION'        => 'R',   //  R = Reactivate
            'ORIGPROFILEID' => $profileid,
            'FIRSTNAME'     => $FIRSTNAME,
            'LASTNAME'      => $LASTNAME,
            'CARDTYPE'      => $CARDTYPE,
            'ACCT'          => $ACCT,
            'EXPDATE'       => $EXPDATE,
            'CVV2'          => $CVV2,
            'CURRENCY'      => 'USD',
            'AMT'           => $AMT,
            'START'         => $billDate,
            'IPADDRESS'     => $_SERVER['REMOTE_ADDR']
        ];

        // call paymentInquiry() of Payflow object & store results in $response
        $response = $payflow->processReactivation($vendor, $user, $partner, $password, $data_array);

        if (!$payflow->get_errors())
        {
            // test
            // echo 'Response array<br>';
            // echo '<pre>';
            // print_r($inquiryResponse);
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
