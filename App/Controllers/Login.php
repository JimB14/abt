<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Mail;
use \App\Models\State;
use \App\Config;
use \App\Models\Broker;
use \App\Models\BrokerAgent;

  /**
   * Login controller
   *
   * PHP version 7.0
   */
  class Login extends \Core\Controller
  {
      /**
       * Before filter
       *
       * @return void
       */
      protected function before()
      {
          if(isset($_SESSION['user']))
          {
              echo "<p>Error. You are logged in.<br>You can manage your password
              in &quot;My Account&quot; in the Admin Panel.</p>";
              exit();
          }
      }


      protected function after()
      {
          //echo " (after)";

      }


      /**
       * Show the Login page
       *
       * @return void
       */
      public function indexAction()
      {
          // display log in page
          View::renderTemplate('Login/index.html', []);
      }




    /**
     * logs in user if matching credentials found
     *
     * @return user object or null
     */
    public function loginUser()
    {
        // retrieve form values
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $password = ( isset($_REQUEST['password'])  ) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo $email . "<br>";
        // echo $password  . "<br>";
        // exit();

        // get visitor IP Address
        $user_ip_address = $this->getUserIp();

        // echo $user_ip_address;

        // validate user & find if in database; store user data in $user object
        $user = User::validateLoginCredentials($email, $password);

        // test
        // echo '<pre>';
        // print_r($user);
        // echo "</pre>";
        // exit();

        // check if superUser
        if($user && $user->superUser == 1)
        {
            // log superUser into SiteAdmin
            // log returning user in
            // create unique id & store in SESSION variable
            $uniqId = md5($user->id);
            $_SESSION['user'] = $uniqId;
            $_SESSION['loggedIn'] = true;

            // assign user ID & access_level & full_name to SESSION variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['access_level'] = $user->access_level;
            $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;
            $_SESSION['superUser'] = 'true';

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            // test
            // echo $_SESSION['user'] . "<br>";
            // echo $_SESSION['loggedIn'] . "<br>";
            // echo $_SESSION['user_id'] . "<br>";
            // echo $_SESSION['access_level'] . "<br>";
            // echo $_SESSION['full_name'] . "<br>";
            // echo $_SESSION['superUser'] . "<br>";
            // exit();

            // get broker data (empty array returned for site admin users)
            $broker = Broker::getBrokerByUserId($user->id);

            if($broker)
            {
                // send login notification email to `brokers`.`broker_email`
                $result = Mail::loginNotification($broker, $user);

            }

            header("Location: /");
            exit();
        }

        // get broker data if available
        $broker = Broker::getBrokerByUserId($user->id);

        if($broker)
        {
          // store broker ID in variable
          $broker_id = $broker->broker_id;

          // get count of agent records for broker by broker ID
          $agent_count = BrokerAgent::getCountOfAgents($broker_id);
        }

        // // test
        // echo '<pre>';
        // print_r($broker);
        // echo "</pre>";
        // echo 'Broker ID: ' . $broker_id . '<br>';
        // echo 'Agent count: ' . $agent_count . '<br>';
        // exit();

        // check if returning user; if true log in
        if( ($user) && ($user->first_login == 0) && ($user->current == 1) )
        {
            // log returning user in
            // create unique id & store in SESSION variable
            $uniqId = md5($user->id);
            $_SESSION['user'] = $uniqId;
            $_SESSION['loggedIn'] = true;

            // assign user ID & access_level & full_name to SESSION variables
            $_SESSION['user_id'] = $user->id;
            $_SESSION['access_level'] = $user->access_level;
            $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;
            $_SESSION['company'] = $user->company;

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            // test
            // echo $_SESSION['user'] . "<br>";
            // echo $_SESSION['loggedIn'] . "<br>";
            // echo $_SESSION['user_id'] . "<br>";
            // echo $_SESSION['access_level'] . "<br>";
            // echo $_SESSION['full_name'] . "<br>";
            // echo $_SESSION['company'] . "<br>";
            // exit();

            // get broker data
            $broker = Broker::getBrokerByUserId($user->id);

            // test
            // echo '<pre>';
            // print_r($broker);
            // echo "</pre>";
            // exit();

            if($broker)
            {
                // send login notification email to `brokers`.`broker_email`
                $result = Mail::loginNotification($broker, $user);

                if($result)
                {
                    echo '<script>';
                    echo 'window.location.href="/"';
                    echo '</script>';
                    exit();
                }
            }
            else
            {
                echo "Error retrieving broker data.";
                exit();
            }
        }

        // user who has paid but never logged in = needs to register company
        if( ($user) && ($user->first_login == 1 && $user->current == 1) )
        {
            // get states for drop-down
            $states = State::getStates();

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // first time logging in (users.first_login === 1)
            View::renderTemplate('Register/new-user-registration.html', [
                'user'   => $user,
                'states' => $states
            ]);
            exit();
        }
        // new susbscriber who has never logged in or paid
        elseif ( ($user) && ($user->first_login == 1 && $user->current == 0) )
        {

            // send for payment; pass action for new subscription
            View::renderTemplate('Paypal/index.html', [
                'user'              => $user,
                'new_subscription'  => 'true',
                'pagetitle'         => 'Subscribe - includes one month FREE',
                'subscriptiononly'  => Config::SUBSCRIPTION,
                'action'            => '/subscribe/process-payment-with-free-trial?id='.$user->id
            ]);
            exit();
        }
        // user who cancelled payment (requires reactivation)
        elseif ( ($user) && ($user->first_login == 0 && $user->current == 0 && $user->active == 1) )
        {

            $pagetitle = 'Reactivate account';

            // calculate new rate
            $reactivation_rate = $agent_count * Config::SUBSCRIPTION;

            // format for PayPal
            $reactivation_rate = number_format($reactivation_rate, 2);

            // test
            // echo '<pre>';
            // print_r($broker);
            // echo "</pre>";
            // echo '$broker_id: ' . $broker_id . '<br>';
            // echo '$agent_count: ' . $agent_count . '<br>';
            // echo '$reactivation_rate: ' . $reactivation_rate . '<br>';
            // echo '$pagetitle: ' . $pagetitle . '<br>';
            // exit();

            // send for payment
            View::renderTemplate('Paypal/index.html', [
                'user'              => $user,
                'pagetitle'         => $pagetitle,
                'reactivate'        => 'true',
                'agent_count'       => $agent_count,
                'reactivation_rate' => $reactivation_rate,
                'new_agent_cost'    => Config::SUBSCRIPTION,
                'subscriptiononly'  => Config::SUBSCRIPTION,
                'action'            => '/subscribe/process-reactivation?user_id='.$user->id
            ]);
            exit();
        }
        elseif ( ($user) && ($user->first_login == 0 && $user->current == 0) )
        {
            // send for payment
            View::renderTemplate('Paypal/index.html', [
                'user'              => $user,
                'new_subscription'  => 'true'
            ]);
            exit();
        }
        else
        {
            echo "Error logging in. Please check credentials and try again.";
            exit();
        }
    }




    public function forgotPassword()
    {
        View::renderTemplate('Login/get-new-password.html', []);
    }




    public static function getNewPassword()
    {
        // Verify that email exists in `users` table
        $email = ( isset($_POST['email_address']) ) ? htmlspecialchars($_POST['email_address']) : '';

        // verify user exists; return $user object
        $user = User::doesUserExist($email);

        // test
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';
        // exit();

        if($user)
        {
            View::renderTemplate('Login/answer-security-questions.html', [
                'user_id' => $user->id
            ]);
        }
        else
        {
            echo "<h3>Error. User not found. Please verify login credentials
            and try again.</h3>";
            exit();
        }
    }



    public static function checkSecurityAnswers()
    {
        // retrieve user ID
        $user_id = ( isset($_REQUEST['id']) ) ?  filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT): '';

        // check answers
        $user = User::checkSecurityAnswers($user_id);

        if ($user)
        {
            // create temp password for next step
            $tmp_pass = bin2hex(openssl_random_pseudo_bytes(4));

            // insert temporary password
            $result = User::insertTempPassword($user->id, $tmp_pass);

            if($result)
            {
                // send email to user; pass $user object & $tmp_pass
                $result = Mail::sendTempPassword($user, $tmp_pass);

                if($result)
                {
                    $message = "A temporary password was sent to your email address.
                      Please use it to log in and reset your password.";

                    View::renderTemplate('Success/index.html', [
                        'message' => $message
                    ]);
                }
                else
                {
                    echo "Unable to send a temporary password. Pleas try again";
                    exit();
                }
            }
            else
            {
                echo "Error occurred. Please try again.";
                exit();
            }
        }
        else
        {
            echo "<h3>One or more answers are incorrect. Please try again.</h3>";
            echo '<h3><a href="/login/forgot-password">Return to try again</a></h3>';
            exit();
        }
    }




    public function tempPassLogin()
    {
        View::renderTemplate('Login/temp-password-login.html', []);
    }




    public function loginUserWithTempPassword()
    {
        // retrieve form values
        $email = ( isset($_REQUEST['email'])  ) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $tmp_pass = ( isset($_REQUEST['tmppassword'])  ) ? filter_var($_REQUEST['tmppassword'], FILTER_SANITIZE_STRING) : '';

        // log in user
        $user = User::loginUserWithTempPassword($email,$tmp_pass);

        if($user)
        {
            // delete tmp_pass from users table
            $result = User::deleteTempPassword($user->id);

            if($result)
            {
                // log user in
                // create unique id & store in SESSION variable
                $uniqId = md5($user->id);
                $_SESSION['user'] = $uniqId;
                $_SESSION['loggedIn'] = true;

                // assign user ID & access_level & full_name to SESSION variables
                $_SESSION['user_id'] = $user->id;
                $_SESSION['access_level'] = $user->access_level;
                $_SESSION['full_name'] = $user->first_name . ' ' . $user->last_name;

                // test
                // echo $_SESSION['user'] . "<br>";
                // echo $_SESSION['loggedIn'] . "<br>";
                // echo $_SESSION['user_id'] . "<br>";
                // echo $_SESSION['access_level'] . "<br>";
                // echo $_SESSION['full_name'] . "<br>";
                // exit();

                header("Location: /");
                exit();
            }
        }
    }


    /**
     * gets visitor's IP address
     * @return [type] [description]
     */
    public function getUserIP()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }


}
