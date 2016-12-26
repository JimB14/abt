<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Mail;
use \App\Models\State;

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

        // validate user & find if in database; store user data in $user object
        $user = User::validateLoginCredentials($email, $password);

        // test
        // echo '<pre>';
        // print_r($user);
        // echo "</pr>";
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

            // session timeout code in front-controller public/index.php
            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

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

        // check if user has ever logged in and is current
        if( ($user) && ($user->first_login == 1 && $user->current == 1) )
        {
            // get states for drop-down
            $states = State::getStates();

            // first time logging in (users.first_login === 1)
            View::renderTemplate('Register/new-user-registration.html', [
                'user'   => $user,
                'states' => $states
            ]);
            exit();
        }
        elseif ( ($user) && ($user->first_login == 1 && $user->current == 0) )
        {
            // get states for drop-down
            $states = State::getStates();

            // send for payment
            View::renderTemplate('Paypal/index.html', [
                'user'   => $user,
                'states' => $states
            ]);
            exit();
        }
        elseif ( ($user) && ($user->first_login == 0 && $user->current == 0) )
        {
            // get states for drop-down
            $states = State::getStates();

            // send for payment
            View::renderTemplate('Paypal/index.html', [
                'user'   => $user,
                'states' => $states
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


}
