<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Broker;
use \App\Mail;
use \App\Models\User;


/**
 * Logout controller
 *
 * PHP version 7.0
 */
class Logout extends \Core\Controller
{
    /**
     * logs user out
     * @return boolean
     */
    public function indexAction()
    {
        // retrieve query string data
        $value = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // if SESSION is not set & user attempts to logout
        if(!isset($_SESSION['user']))
        {
            header("Location: /login");
            exit();
        }
        else if($value == 'cancel')
        {
            $cancel_message1 = "Your account has been canceled. Your credit card
              will not be charged again.";

            $cancel_message2 = "We are sorry to see you go.";

            $cancel_message3 = "Your data will be deleted in the next 4 - 5 days.
              If you want to reactivate your account, just Log In and follow the
              instructions.";

              unset($_SESSION['user']);
              unset($_SESSION['loggedIn']);
              unset($_SESSION['user_id']);
              unset($_SESSION['access_level']);
              unset($_SESSION['full_name']);
              session_destroy();

              // render view
              View::renderTemplate("Success/index.html", [
                  'cancelpayment'   => 'true',
                  'cancel_message1' => $cancel_message1,
                  'cancel_message2' => $cancel_message2,
                  'cancel_message3' => $cancel_message3,
              ]);
        }
        else
        {

            // get broker data
            $broker = Broker::getBrokerByUserId($_SESSION['user_id']);

            // get user data
            $user = User::getUser($_SESSION['user_id']);

            if($broker)
            {
                // send login notification email to `brokers`.`broker_email`
                $result = Mail::LogoutNotification($broker, $user);
            }

            unset($_SESSION['user']);
            unset($_SESSION['loggedIn']);
            unset($_SESSION['user_id']);
            unset($_SESSION['access_level']);
            unset($_SESSION['full_name']);
            session_destroy();

            $message = "You have been logged out";

            // render view
            View::renderTemplate("Success/index.html", [
                'message'     => $message,
            ]);
        }
    }

}
