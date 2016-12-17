<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Models\UserPending;
use \App\Mail;
use \App\Models\Broker;



class Register extends \Core\Controller
{

    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //echo "(before) ";
        //return false;  // prevents originally called method from executing
    }


    protected function after()
    {
        //echo " (after)";

    }



    public function indexAction()
    {
        View::renderTemplate('Register/index.html', []);
    }



    public function checkIfEmailAvailableAction()
    {
        // store POST variable from Ajax script
        $email = $_POST['email'];

        // check for email using User model method
        $response = User::checkIfAvailable($email);

        // return $response value ('available' or 'not available') to
        // Ajax method for processing
        echo $response;
    }




    /**
     * adds new user to users table, users_pending table & sends verification email
     *
     * @return void
     */
    public function registerNewUser()
    {
        // add new user to users table; get data & store in $results array
        $results = User::addNewUser();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // echo "<br><br>";
        // exit();

        // assign values from $results associative array to variables
        $token = $results['token'];
        $user_id = $results['user_id'];

        // get all fields of new user data (need email & first_name)
        $user = User::getUser($user_id);

        // assign values from user object to variables
        $email = $user->email;
        $first_name = $user->first_name;

        // test
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';
        // echo '<br><br>';
        // exit();

        // add new user to users_pending table & pass $token & $user_id
        $results = $this->addToUsersPending($token, $user_id);

        if($results)
        {
            // send verification email to new user's email address
            $result = Mail::sendAccountVerificationEmail($token, $user_id, $email, $first_name);

            if($result)
            {
              // define message
              $message = "You have successfully registered! Please check your
                          email for our verification email.";

              View::renderTemplate('Success/index.html', [
                  'message' => $message
              ]);
            }
            else
            {
                echo "Error. Verification email not sent";
                exit();
            }
        }
    }



    /**
     * adds new user to users_pending table
     *
     * @param string $token     Unique string
     * @param integer $user_id  The new user's ID
     */
    public function addToUsersPending($token, $user_id)
    {
        // add user data to users_pending table
        $results = UserPending::addUserToUsersPending($token, $user_id);

        // return to Controller
        return $results;
    }





    /**
     * new user clicks link in email
     *
     * @return string boolean
     */
    public function verifyAccount()
    {
        // retrieve token & user_id & pass to verifyNewUserAccount method below
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $user_id = isset($_REQUEST['user_id']) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // verify match in `users_pending`, if true, set active = 1
        $result = UserPending::verifyNewUserAccount($token, $user_id);

        // display success in view
        if($result)
        {
            // define message
            $message = "Congratulations! Your account has been activated. You can now login.";

            // show success message
            View::renderTemplate('Success/index.html', [
                'message' => $message
            ]);
        }
        else
        {
            echo "An error occurred while verifying your account. Please try again.";
            exit();
        }
    }




    public function brokerRegistration()
    {
        // retrieve token in order to pass to verifyNewUserAccount method below
        $user_id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // insert user's broker data into brokers table; get new Broker's ID
        $result = Broker::addNewBroker($user_id);

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        if($result)
        {
            // render security questions form
            View::renderTemplate('Register/security-questions.html', [
                'user_id' => $user_id
            ]);
        }
    }




    public function postSecurityAnswers()
    {
        // retrieve query string variable
        $user_id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // store security answers in users table
        $result = User::storeSecurityAnswers($user_id);

        if($result)
        {
            // update first_login status to false (first_login = 0)
            $result = User::updateFirstLoginStatus($user_id);
        }

        if($result)
        {
          $message = "Information successfully uploaded! You can now log in.";

          // display success message
          echo '<script>';
          echo 'alert("'.$message.'")';
          echo '</script>';

          // redirect user to "Manage agents" page
          echo '<script>';
          echo 'window.location.href="/login"';
          echo '</script>';
          exit();
        }
        else
        {
            $message = "Error. Please try again.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/login"';
            echo '</script>';
            exit();
        }
    }


}
