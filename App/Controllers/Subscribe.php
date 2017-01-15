<?php

namespace App\Controllers;

use \App\Config;
use \App\Models\Payflow;
use \App\Models\Paypal;
use \App\Models\Paypallog;
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
          $result = Paypallog::addTransactionData($user_id, $data_array);

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


    /**
     * modifies paypal profile
     *
     * @return [type] [description]
     */
    public function processPaymentForNewAgents()
    {
        // retrieve user ID from query string
        $user_id = (isset($_GET['id'])) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $profileid = (isset($_GET['profileid'])) ? filter_var($_GET['profileid'], FILTER_SANITIZE_STRING) : '';
        $current_agent_count = (isset($_GET['maxagents'])) ? filter_var($_GET['maxagents'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo "Connected to processPaymentForNewAgents() method in Subscribe Controller!<br><br>";
        // echo $user_id .'<br>';
        // echo $profileid.'<br>';
        // echo $agent_count.'<br>';
        // echo $max_agents .'<br>';
        // exit();

        // process the payment; get back response
        $results = Paypal::processPaymentModification($user_id, $profileid);

        // store array content in variables
        $response = $results['response'];
        $agents_added = $results['agents_added'];
        $new_amount = $results['new_amount'];

        // if successful
        if($response)
        {
            // get new amount from PayPal for this profileid
            $inquiryResponse = Paypal::profileStatusInquiry($profileid);

            // store AMT from response associative array in variable
            $returned_amount = $inquiryResponse['AMT'];

            // store NEXTPAYMENT from response array in variable
            $next_payment = $inquiryResponse['NEXTPAYMENT'];

            // store RegExp values in variables
            $pattern     = '/(\d{2})(\d{2})(\d{4})/';
            $replacement = '\1-\2-\3';

            // re-format (add hyphens) using RegExp for better readability
            $next_payment  = preg_replace($pattern, $replacement, $next_payment);

            // test if PayPal AMT equals expected amount
            // echo 'PP INQUIRY AMT: ' . $returned_amount . '<br>';
            // echo '$new_amount: ' . $new_amount . '<br>';
            // echo '<pre>';
            // print_r($inquiryResponse);
            // echo '</pre>';
            // exit();

            // store PP response data in array
            $data_array = [
                'RESULT'    => $response['RESULT'],
                'RESPMSG'   => $response['RESPMSG'],
                'RPREF'     => $response['RPREF'],
                'PROFILEID' => $response['PROFILEID'],
                'AMT'       => $returned_amount
            ];

            // store transaction response data in paypal_log
            $result = Paypallog::addTransactionDataFromModification($user_id, $data_array);

            if($result)
            {
                // calculate number of agents added ( (new billing / cost per agent) - 1 (free))
                $agents_added = ($returned_amount/Config::SUBSCRIPTION) - 1;

                // calculate new max_agents value to update `users`.`max_agents`
                $new_max_agents = $current_agent_count + $agents_added;

                // test
                // echo $current_agent_count . '<br>';
                // echo $returned_amount . '<br>';
                // echo $agents_added . '<br>';
                // echo $new_max_agents . '<br>';
                // exit();

                // update users table
                $result = User::updateUserAfterAddingAgents($user_id, $new_max_agents, $returned_amount);

                if($result)
                {
                    // get updated user data
                    $user = User::getUser($user_id);

                    // define message based on number of agents
                    if($user->max_agents < 2)
                    {
                        $subscribe_msg1 = "You have successfully increased your agent limit
                        to $user->max_agents additional agent!";
                    }
                    else
                    {
                        $subscribe_msg1 = "You have successfully increased your agent limit
                        to $user->max_agents agents!";
                    }

                    $subscribe_msg2 = "Your credit card will be charged $$returned_amount
                    on $next_payment and each month after unless you cancel your
                    subscription.";

                    $subscribe_msg3 = "You can now add a new agent profile.";

                    View::renderTemplate('Success/index.html', [
                        'subscribe_success' => 'true',
                        'subscribe_msg1'    => $subscribe_msg1,
                        'subscribe_msg2'    => $subscribe_msg2,
                        'subscribe_msg3'    => $subscribe_msg3,
                        'first_name'        => $user->first_name,
                        'last_name'         => $user->last_name
                    ]);
                }
                else
                {
                    echo "An error occurred while updating user data.";
                    exit();
                }
            }
            else
            {
                echo "An error occurred while adding transaction data to payment log.";
                exit();
            }
        }
        else
        {
            echo "An error occurred while processing your payment.";
            exit();
        }
    }




    public function processPaymentReduction()
    {
        // echo "Connect to processPaymentReduction() in Subscribe Controller!<br><br>";

        // retrieve user ID from query string & form post data ($agent_count)
        $user_id       = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $profileid     = (isset($_REQUEST['profileid'])) ? filter_var($_REQUEST['profileid'], FILTER_SANITIZE_STRING) : '';
        $agent_count   = (isset($_REQUEST['remove_agent_count'])) ? filter_var($_REQUEST['remove_agent_count'], FILTER_SANITIZE_NUMBER_INT) : '';
        $current_amt   = (isset($_REQUEST['current_amt'])) ? filter_var($_REQUEST['current_amt'], FILTER_SANITIZE_STRING) : '';

        // check for positive value in $agent_count - backup for JavaScript failure
        if($agent_count < 1)
        {
            echo "Please select an amount in 'Agents to remove' drop-down.";
            exit();
        }

        // Convert agent count into reduction value
        $reduction = number_format( ($agent_count * Config::SUBSCRIPTION), 2);
        $new_amt = number_format( ($current_amt - $reduction), 2);

        // test
        // echo 'user_id: ' . $user_id . '<br>';
        // echo 'profileid: ' . $profileid . '<br>';
        // echo 'agent_count: ' . $agent_count . '<br>';
        // echo 'current_amt: ' . $current_amt . '<br>';
        // echo 'reduction: ' . $reduction . '<br>';
        // echo 'new_amt: ' . $new_amt . '<br><br>';

        // process the new payment amount using passed parameters; get back response
        $results = Paypal::processPaymentReduction($user_id, $profileid, $new_amt);

        // test
        // echo "PayPal response:";
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';

        // if successful
        if($results)
        {
            // get new amount from PayPal for this profileid
            $inquiryResponse = Paypal::profileStatusInquiry($profileid);

            // store AMT from response associative array in variable
            $returned_amount = $inquiryResponse['AMT'];

            // store NEXTPAYMENT from response associative array in variable
            $next_payment = $inquiryResponse['NEXTPAYMENT'];

            // store RegExp values in variables
            $pattern     = '/(\d{2})(\d{2})(\d{4})/';
            $replacement = '\1-\2-\3';

            // re-format (add hyphens) using RegExp for better readability
            $next_payment  = preg_replace($pattern, $replacement, $next_payment);

            // test if PayPal AMT equals expected amount
            // echo 'PP INQUIRY AMT: ' . $returned_amount . '<br>';
            // echo '$new_amt: ' . $new_amt . '<br>';
            // echo '<pre>';
            // print_r($inquiryResponse);
            // echo '</pre>';
            // exit();

            // store PayPal response data in array
            $data_array = [
                'RESULT'      => $results['RESULT'],
                'RESPMSG'     => $results['RESPMSG'],
                'RPREF'       => $results['RPREF'],
                'PROFILEID'   => $results['PROFILEID'],
                'AMT'         => $returned_amount
            ];

            // store transaction response data in paypal_log
            $result = Paypallog::addTransactionDataFromModification($user_id, $data_array);

            if($result)
            {
                // get user data
                $user = User::getUser($user_id);

                // store value of max_agents in variable
                $current_agent_count = $user->max_agents;

                // calculate number of agents deducted ( (new billing amount / cost per agent) - 1 (free))
                //$agents_deducted = ($returned_amount/Config::SUBSCRIPTION) - 1;

                // calculate new max_agents value to update `users`.`max_agents`
                $new_max_agents = $current_agent_count - $agent_count;

                // test
                // echo $current_agent_count . '<br>';
                // echo $returned_amount . '<br>';
                // echo $agent_count . '<br>';
                // echo $new_max_agents . '<br>';
                // exit();

                // update users table
                $result = User::updateUserAfterDeductingAgents($user_id, $new_max_agents, $returned_amount);

                if($result)
                {
                    // get updated user data
                    $user = User::getUser($user_id);

                    // define message based on number of agents
                    if($user->max_agents < 2)
                    {
                        $subscribe_msg1 = "You have successfully decreased your
                        agent limit to $user->max_agents agent!";
                    }
                    else
                    {
                        $subscribe_msg1 = "You have successfully decreased your
                        agent limit to $user->max_agents agents!";
                    }

                    $subscribe_msg2 = "Your credit card will be charged $$returned_amount
                    on $next_payment and each month after unless you cancel your
                    subscription.";

                    $subscribe_msg3 = "You can verify these changes in the Admin
                    Panel by clicking on 'My account' under 'Company'.";

                    View::renderTemplate('Success/index.html', [
                        'subscribe_success' => 'true',
                        'subscribe_msg1'    => $subscribe_msg1,
                        'subscribe_msg2'    => $subscribe_msg2,
                        'subscribe_msg3'    => $subscribe_msg3,
                        'first_name'        => $user->first_name,
                        'last_name'         => $user->last_name
                    ]);
                }
                else
                {
                    echo "An error occurred while updating user data.";
                    exit();
                }
            }
            else
            {
                echo "An error occurred while adding transaction data to payment log.";
                exit();
            }
        }
        else
        {
            echo "An error occurred while processing your payment.";
            exit();
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
            $result = Paypallog::addCancelTransactionData($user_id, $data_array);

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
