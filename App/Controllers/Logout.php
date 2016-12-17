<?php

namespace App\Controllers;

use \Core\View;


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
            unset($_SESSION['user']);
            unset($_SESSION['loggedIn']);
            unset($_SESSION['user_id']);
            unset($_SESSION['access_level']);
            unset($_SESSION['full_name']);
            session_destroy();

            $message = "You have been logged out";

            View::renderTemplate('Success/index.html', [
                'message' => $message
            ]);
        }
    }
}
