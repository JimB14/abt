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
use \App\Models\BrokerAgent;


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

        // test
        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';
        // exit();

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

            // store subscription amount in variable
            $sub_amt = $data_array['AMT'];

            // store transaction response data in paypal_log
            $result = Paypallog::addTransactionData($user_id, $data_array);

            if($result)
            {
                // modify users.current field to true (1)
                $result = User::updateCurrent($user_id, $current=1, $sub_amt, $max_agents=1);

                if($result)
                {
                    // get user data
                    $user = User::getUser($user_id);

                    // define message
                    $subscribe_msg1 = "You have successfully paid for your first
                    month's subscription!";

                    $subscribe_msg2 = "Your credit card will be charged for the
                    same amount ($$sub_amt) one month from tomorrow and each
                    month afterward unless you cancel your subscription.";

                    $subscribe_msg3 = "You can now Log In to complete the
                    registration process and begin posting your listings.";

                    $subscribe_msg4 = "Congratulations and welcome to American
                      Biz Trader!";

                    $subscribe_msg5 = "You can Log In here.";

                    View::renderTemplate('Success/index.html', [
                        'subscribe_success' => 'true',
                        'subscribe_msg1'    => $subscribe_msg1,
                        'subscribe_msg2'    => $subscribe_msg2,
                        'subscribe_msg3'    => $subscribe_msg3,
                        'subscribe_msg4'    => $subscribe_msg4,
                        'subscribe_msg5'    => $subscribe_msg5,
                        'first_name'        => $user->first_name,
                        'last_name'         => $user->last_name
                    ]);
                }
                else
                {
                    echo "Error updating user data.";
                    exit();
                }
            }
            else
            {
                echo "Error inserting transaction data.";
                exit();
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
        $current_max_agent_value = (isset($_GET['maxagents'])) ? filter_var($_GET['maxagents'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo "Connected to processPaymentForNewAgents() method in Subscribe Controller!<br><br>";
        // echo $user_id .'<br>';
        // echo $profileid.'<br>';
        // echo $agent_count.'<br>';
        // echo $max_agents .'<br>';
        // exit();

        // process the payment; get back response
        $results = Paypal::processPaymentForNewAgents($profileid);

        // store array content in variables
        $response = $results['response'];
        $agents_added = $results['agents_added'];
        $new_amount = $results['new_amount'];

        // test
        // echo 'PP results:';
        // echo '<pre>';
        // echo print_r($results);
        // echo '</pre>';
        // exit();

        // if successful
        if($response)
        {
            // get new amount from PayPal for this profileid
            $inquiryResponse = Paypal::profileStatusInquiry($profileid);

            // test
            // echo 'Inquiry response:';
            // echo '<pre>';
            // print_r($inquiryResponse);
            // echo '</pre>';
            // exit();

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
                // calculate number to update in max_agents ( (new billing / cost per agent)
                $new_max_agents = $returned_amount/Config::SUBSCRIPTION;

                // calculate new max_agents value to update `users`.`max_agents`
                //$new_max_agents = $current_max_agent_value + $agents_added;

                // test
                // echo $current_max_agent_value . '<br>';
                // echo $returned_amount . '<br>';
                // echo $agents_added . '<br>';
                // echo $new_max_agents . '<br>';
                // exit();

                // update users table
                $result = User::updateUserAfterAddingAgents($user_id, $new_max_agents, $returned_amount);

                if($result)
                {
                    // get broker data
                    $broker = Broker::getBrokerByUserId($user_id);

                    // store broker ID in variable
                    $broker_id = $broker->broker_id;

                    // get updated user data
                    $user = User::getUser($user_id);

                    // define message based on number of agents
                    if($user->max_agents < 2)
                    {
                        $added_agent_success1 = "You have successfully increased your agent limit
                        to $user->max_agents additional agent!";
                    }
                    else
                    {
                        $added_agent_success1 = "You have successfully increased your agent limit
                        to $user->max_agents agents!";
                    }

                    $added_agent_success2 = "Your credit card will be charged $$returned_amount
                    on $next_payment and each month on the same date unless you
                    cancel your subscription.";

                    $added_agent_success3 = "You can now add a new agent profile.";

                    $added_agent_success4 = "Add new agent.";

                    View::renderTemplate('Success/index.html', [
                        'added_agent_success'   => 'true',
                        'added_agent_success1'  => $added_agent_success1,
                        'added_agent_success2'  => $added_agent_success2,
                        'added_agent_success3'  => $added_agent_success3,
                        'added_agent_success4'  => $added_agent_success4,
                        'first_name'            => $user->first_name,
                        'last_name'             => $user->last_name,
                        'broker_id'             => $broker_id
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
                $current_max_agent_value = $user->max_agents;

                // calculate number of agents deducted ( (new billing amount / cost per agent) - 1 (free))
                //$agents_deducted = ($returned_amount/Config::SUBSCRIPTION) - 1;

                // calculate new max_agents value to update `users`.`max_agents`
                $new_max_agents = $current_max_agent_value - $agent_count;

                // test
                // echo $current_max_agent_value . '<br>';
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


    /**
     * sends user to page to change credit cards
     *
     * @return view
     */
    public function authorizeNewCreditCard()
    {
        // echo "Connect to authorizeNewCreditCard() in Subscribe Controller!<br><br>";

        // retrieve user ID & profileid from post data
        $user_id   = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $profileid = (isset($_REQUEST['profileid'])) ? filter_var($_REQUEST['profileid'], FILTER_SANITIZE_STRING) : '';

        if($user_id == '' || $profileid == '')
        {
            echo '<script>';
            echo 'alert("Error. You must refresh the window before re-submitting another \'View details\' request.")';
            echo '</script>';

            echo '<script>';
            echo 'window.location.href="/admin/brokers/my-account?id={{ session.broker_id }}"</script>';
            echo '</script>';
        }

        // test
        // echo 'user_id: ' . $user_id . '<br>';
        // echo 'profileid: ' . $profileid . '<br><br>';
        // exit();

        // set page title
        $pagetitle = 'Authorize new credit card';

        $explain = 'If approved, this card will be charged on the same monthly billing date.';

        View::renderTemplate('Paypal/index.html', [
            'user_id'     => $user_id,
            'profileid'   => $profileid,
            'update-card' => 'true',
            'pagetitle'   => $pagetitle,
            'explain'     => $explain,
            'action'      => '/subscribe/process-credit-card-authorization?user_id='.$user_id.'&profileid='.$profileid
        ]);
    }



    public function processCreditCardAuthorization()
    {
        // retrieve query string data
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $profileid = (isset($_REQUEST['profileid'])) ? filter_var($_REQUEST['profileid'], FILTER_SANITIZE_STRING) : '';

        // get user data
        $user = User::getUser($user_id);

        // message and redirect if query string data incomplete
        if($user_id == '' || $profileid == '')
        {
            echo '<script>';
            echo 'alert("Error. Data missing.';
            echo '</script>';

            echo '<script>';
            echo 'window.location.href="/admin/brokers/my-account?id={{ session.broker_id }}"</script>';
            echo '</script>';
        }

        // test
        // echo $user_id . '<br>';
        // echo $profileid . '<br>';
        // exit();

        // process credit card authorization (need PNREF)
        $result = Paypal::authorizeCreditCard();

        if($result)
        {
            // test
            // echo '<pre>';
            // print_r($result);
            // echo '</pre>';

            // store PNREF value in variable
            $pnref = $result['PNREF']; // PNREF value is the ORIGID of an original transaction used to update credit card account information

            // test
            // echo $pnref . '<br>';
            // echo $profileid . '<br>';

            // update user with new credit card data
            $result = Paypal::updateUserProfileWithNewCardData($profileid, $pnref);

            if($result)
            {
                // test
                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';
                // exit();

                // store PayPal response values in array
                $data_array = [
                    'RESULT'    => $result['RESULT'],
                    'RPREF'     => $result['RPREF'],
                    'PROFILEID' => $result['PROFILEID'],
                    'RESPMSG'   => $result['RESPMSG']
                ];

                // update Paypal log
                $result = Paypallog::addTransactionDataForCreditCardUpdate($user_id, $data_array);

                if($result)
                {
                    // store message to display to user
                    $new_card_authorized1 = "Your new card is authorized for use!";

                    // store message to display to user
                    $new_card_authorized2 = "On your recurring billing date, your
                    next payment will be charged against this card.";

                    // store message to display to user
                    $new_card_authorized3 = "You can change credit cards at any
                      time in the Admin Panel in 'My account.'";

                    // rendier view
                    View::renderTemplate("Success/index.html", [
                        'new_card_authorized1'  => $new_card_authorized1,
                        'new_card_authorized2'  => $new_card_authorized2,
                        'new_card_authorized3'  => $new_card_authorized3,
                        'first_name'            => $user->first_name,
                        'last_name'             => $user->last_name,
                        'new_card_authorized'   => 'true'
                    ]);
                }
                else
                {
                    echo "Error updating log with transaction data.";
                    exit();
                }
            }
            else
            {
                echo "Error updating profile with new credit card data.";
                exit();
            }
        }
        else
        {
            echo "Error authorizing credit card.";
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
                'RPREF'     => $response['RPREF'],     // Reference number to this particular action request.
                'PROFILEID' => $response['PROFILEID'], // profile ID of the original profile
                'RESPMSG'   => $response['RESPMSG']    // Optional response message.
                //'AUTHCODE'  => $response['AUTHCODE']
            ];

            // store transaction response data in paypal_log
            $result = Paypallog::addCancelTransactionData($user_id, $data_array);

            if($result)
            {
                // update users.current to false ('0') & sub_amt to $0.00
                $result = User::updateUserAccount($user_id, $current=0, $sub_amt=0, $max_agents=0);

                if($result)
                {
                    // get broker data
                    $broker = Broker::getBrokerByUserId($user_id);

                    // store broker ID in variable
                    $broker_id = $broker->broker_id;

                    // set business listings to not display
                    $result = Listing::updateBusinessListingsDisplayToFalse($broker_id);

                    if($result)
                    {
                        // set agents to not display
                        $result = BrokerAgent::updateAgentsDisplayToFalse($broker_id);

                        if($result)
                        {
                            // set realty listings to not display
                            $result = Realtylisting::updateRealtyListingsDisplayToFalse($broker_id);

                            if($result)
                            {
                                // log user out
                                header("Location: /logout");
                                exit();
                            }
                            else
                            {
                                echo "Error updating realty listing data.";
                                exit();
                            }
                        }
                        else
                        {
                            echo "Error updating agents data.";
                            exit();
                        }
                    }
                    else
                    {
                        echo "Error updating listings data.";
                        exit();
                    }
                }
                else
                {
                    echo "Error updating user data.";
                    exit();
                }
            }
            else
            {
                echo "Error adding data to log.";
                exit();
            }
        }
        else
        {
            echo "Error cancelling payment.";
            exit();
        }
    }



    public function processReactivation()
    {
        // echo "Connected to processReactivation() in Subscribe Controller.<br><br>";

        $user_id = (isset($_REQUEST['user_id']))? filter_var($_REQUEST['user_id'],FILTER_SANITIZE_NUMBER_INT) : '';

        // get user data
        $user = User::getUser($user_id);

        // get broker data
        $broker = Broker::getBrokerByUserId($user_id);

        // assign broker ID to variable
        $broker_id = $broker->broker_id;

        // get broker agent count & store in $max_agents
        $agent_count = BrokerAgent::getCountOfAgents($broker_id);

        // get PROFILEID for user
        $profileid = User::getProfileId($user_id);

        // process reactivation
        $result = Paypal::processReactivation($profileid);

        if($result)
        {
            // test
            // echo '<pre>';
            // print_r($result);
            // echo '</pre>';
            // exit;

            // store PayPal response values in array
            $data_array = [
                'RESULT'    => $result['RESULT'],
                'RPREF'     => $result['RPREF'],
                'PROFILEID' => $result['PROFILEID'],
                'RESPMSG'   => $result['RESPMSG']
            ];

            // update Paypal log
            $result = Paypallog::addTransactionDataForCreditCardUpdate($user_id, $data_array);

            if($result)
            {
                // account inquiry
                $inquiryResponse = Paypal::profileStatusInquiry($profileid);

                if($inquiryResponse)
                {
                    // test
                    // echo '<pre>';
                    // print_r($inquiryResponse);
                    // echo '</pre>';
                    // exit();

                    // update user table
                    $result = User::updateAfterReactivation($user_id, $current=1, $sub_amt=$inquiryResponse['AMT'], $max_agents=$agent_count);

                    if($result)
                    {
                        // update broker_agents table & set display to true
                        $result = BrokerAgent::setAgentsToDisplay($broker_id);

                        if($result)
                        {
                            // store message to display to user
                            $account_reactivated1 = "Your account has been reactivated! You
                              can Log In and manage your data.";

                            // store message to display to user
                            $account_reactivated2 = "Your next payment is due one
                              month from tomorrow. On that day and in each month afterward
                              your credit card will be charged unless you cancel
                              your subscription.";

                            // store message to display to user
                            $account_reactivated3 = "You can change credit cards at any
                              time in the Admin Panel in 'My account.'";

                            // store message to display to user
                            $account_reactivated4 = "Welcome back! You may now Log In.";

                            // rendier view
                            View::renderTemplate("Success/index.html", [
                                'account_reactivated1'  => $account_reactivated1,
                                'account_reactivated2'  => $account_reactivated2,
                                'account_reactivated3'  => $account_reactivated3,
                                'account_reactivated4'  => $account_reactivated4,
                                'first_name'            => $user->first_name,
                                'last_name'             => $user->last_name,
                                'account_reactivated'   => 'true'
                            ]);
                        }
                        else
                        {
                            echo "Error updating agent data.";
                            exit();
                        }
                    }
                    else
                    {
                        echo "Error updating user data.";
                        exit();
                    }
                }
                else
                {
                    echo "Error retrieving inquiry request.";
                    exit();
                }
            }
            else
            {
                echo "Error updating log with transaction data.";
                exit();
            }
        }
        else
        {
            echo "Error reactivating your account.";
            exit();
        }
    }

}
