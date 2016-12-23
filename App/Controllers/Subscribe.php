<?php

namespace App\Controllers;

use \App\Config;
use \App\Models\Payflow;
use \App\Models\Paypal;
use \App\Models\User;
use \Core\View;


class Subscribe extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        // echo "(before) ";
        // return false;  // prevents originally called method from executing
    }


    protected function after()
    {
        //echo " (after)";
        //return false;  // prevents originally called method from executing

    }


    public function processPayment()
    {
        // retrieve user ID from query string
        $user_id = (isset($_GET['id'])) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : '';

        // echo "Connected to processPayment() method in Subscribe Controller!<br><br>";
        // exit();

        // process the payment; get back response
        $response = Paypal::processPayment($user_id);

        if($response)
        {
          // store PP response data in array
          $data_array = [
              'TRXPNREF'      => $response['TRXPNREF'],
              'PPREF'         => $response['PPREF'],
              'PROFILEID'     => $response['PROFILEID'],
              'CORRELATIONID' => $response['CORRELATIONID'],
              'TRANSTIME'     => $response['TRANSTIME']
          ];

          // store transaction response data in paypal_log
          $result = Paypal::addTransactionData($user_id, $data_array);

          if(!$result)
          {
              // if error occurs
              echo "Error inserting transaction data.";
              exit();
          }
          else
          {
              // modify users.current field to true (1)
              $result = User::updateCurrent($user_id);

              // get user data
              $user = User::getUser($user_id);

              // define message
              $subscribe_msg1 = "You have successfully paid for your first
              month's subscription!";

              $subscribe_msg2 = "Your credit card will be charged for the same
              amount ($9.95) one month from tomorrow and each month thereafter unless
              you cancel your subscription.";

              $subscribe_msg3 = "You can now login to complete the registration
              process and begin posting your listings.";

              $subscribe_msg4 = "Congratulations and welcome to American Biz Trader!";

              View::renderTemplate('Success/index.html', [
                  'subscribe_success'  => 'true',
                  'subscribe_msg1'     => $subscribe_msg1,
                  'subscribe_msg2'     => $subscribe_msg2,
                  'subscribe_msg3'     => $subscribe_msg3,
                  'subscribe_msg4'     => $subscribe_msg4,
                  'first_name'         => $user->first_name,
                  'last_name'          => $user->last_name
              ]);
          }
        }
    }

}
