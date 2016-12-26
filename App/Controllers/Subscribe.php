<?php

namespace App\Controllers;

use \App\Config;
use \App\Models\Payflow;
use \App\Models\Paypal;
use \App\Models\User;
use \Core\View;
use \App\Models\Broker;
use \App\Models\Listing;
use \App\Models\Realtylisting;


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

        // test
        // echo "Connected to processPayment() method in Subscribe Controller!<br><br>";
        // exit();

        // process the payment; get back response
        $response = Paypal::processPayment($user_id);

        // if successful
        if($response)
        {
          // store PP response data in array
          $data_array = [
              'RESULT'        => $response['RESULT'],
              'RESPMSG'       => $response['RESPMSG'],
              'RPREF'         => $response['RPREF'],
              'TRXPNREF'      => $response['TRXPNREF'],
              'PPREF'         => $response['PPREF'],
              'PROFILEID'     => $response['PROFILEID'],
              'CORRELATIONID' => $response['CORRELATIONID'],
              'TRANSTIME'     => $response['TRANSTIME'],
              'AMT'           => $response['AMT']
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
              $result = User::updateCurrent($user_id, $status=1);

              // get user data
              $user = User::getUser($user_id);

              // define message
              $subscribe_msg1 = "You have successfully paid for your first
              month's subscription!";

              $subscribe_msg2 = "Your credit card will be charged for the same
              amount ($9.95) one month from tomorrow and each month thereafter unless
              you cancel your subscription.";

              $subscribe_msg3 = "You can now log in to complete the registration
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



    public function cancelPayment()
    {
        // echo "Successfully connected to cancelPayment() method in Subscribe Controller <br><br>";

        // retrieve query string values
        $user_id = (isset($_GET['user_id'])) ? filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $origprofileid = (isset($_GET['profileid'])) ? filter_var($_GET['profileid'], FILTER_SANITIZE_STRING): '';

        // test
        // echo $user_id . '<br>';
        // echo $origprofileid . '<br>';
        // exit();

        // cancel payment
        $response = Paypal::cancelPayment($user_id, $origprofileid);

        // test
        // echo 'From Subscribe Controller: Response array<br>';
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

        // if successful
        if($response)
        {
            // store PP response data in array
            // resource: https://developer.paypal.com/docs/classic/payflow/recurring-billing/#returned-values-for-the-cancel-action
            $data_array = [
                'RESULT'    => $response['RESULT'],
                'RPREF'     => $response['RPREF'],  // Reference number to this particular action request.
                'PROFILEID' => $response['PROFILEID'], // profile ID of the original profile
                'RESPMSG'   => $response['RESPMSG']  // Optional response message.
                //'AUTHCODE'  => $response['AUTHCODE']
            ];

            // store transaction response data in paypal_log
            $result = Paypal::addCancelTransactionData($user_id, $data_array);

            if(!$result)
            {
                // if error occurs
                echo "Error inserting transaction data.";
                exit();
            }
            else
            {
                // update users.current to false ('0')
                $result = User::updateCurrent($user_id, $status=0);

                if(!$result)
                {
                    // if error occurs
                    echo "Error updating current status.";
                    exit();
                }
                else
                {
                    // get broker data
                    $broker = Broker::getBrokerData($user_id);

                    // store broker ID in variable
                    $broker_id = $broker->broker_id;

                    // set business listings to not display
                    $result = Listing::updateBusinessListingsDisplayToFalse($broker_id);

                    if(!$result)
                    {
                        // if error occurs
                        echo "Error updating listing display status.";
                        exit();
                    }
                    else
                    {
                        // set realty listings to not display
                        $result = Realtylisting::updateRealtyListingsDisplayToFalse($broker_id);

                        if(!$result)
                        {
                            // if error occurs
                            echo "Error updating real estate listings display status.";
                            exit();
                        }
                        else
                        {
                            $usubscribe_message1 = "You have successfully cancelled
                            your subscription.";

                            $usubscribe_message2 = "Your listings will be deleted
                            in 3 - 4 days. If you want to re-subscribe without
                            re-entering your listings and/or agent data, please
                            contact us as soon as possible to re-instate your
                            subscription.";

                            View::renderTemplate("Success/index.html", [
                                'unsubscribe_message1'  => $usubscribe_message1,
                                'unsubscribe_message2'  => $usubscribe_message2,
                                'unsubscribe'           => 'true'
                            ]);
                        }
                    }
                }
            }
        }
    }

}
