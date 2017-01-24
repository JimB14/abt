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
    public function indexAction()
    {
        //if SESSION is not set & user attempts to logout
        if(!isset($_SESSION['user']))
        {
            header("Location: /login");
            exit();
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

            // $usubscribe_message1 = "You have successfully cancelled
            // your subscription. Sorry to see you go.";
            //
            // $usubscribe_message2 = "Your listings might be deleted
            // in 3 - 4 days. If you want to reactivate your account now to avoid
            // having to re-enter your listings and/or agent data in the future, please
            // Log In now and follow the reactivate account instructions.";

            View::renderTemplate("Success/index.html", [
                'message'               => $message,
                // 'unsubscribe_message1'  => $usubscribe_message1,
                // 'unsubscribe_message2'  => $usubscribe_message2,
                'unsubscribe'           => 'true'
            ]);
        }
    }
}
