<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Page;
use \App\Mail;
use \App\Config;
use \App\Models\Recaptcha;



/**
 * Contact controller
 *
 * PHP version 7.0
 */
class Contact extends \Core\Controller
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
      // retrieve string query
      $id = ( isset($_REQUEST['id']) ) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING): '';

      if($id === 'advertise')
      {
          $message = 'Please contact me about advertising on American Biz Trader';
      }
      else if($id === 'help-posting')
      {
          $message = 'Please contact me regarding data entry';
      }
      else if($id === 'feedback')
      {
          $message = "Here's my feedback:";
      }
      else
      {
          $message = '';
      }

       View::renderTemplate('Contact/index.html', [
          'message'   => $message,
          'site_key'  => Config::RECAPTCHASITEKEY
       ]);
    }




    public function submitContact()
    {
        // run Google ReCAPTCHA
        $data = Recaptcha::recaptcha();

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // exit();

        if($data)
        {
            $success = $data->success;
        }
        else
        {
            echo "Error. Please try again.";
            exit();
        }

        // if recaptcha success = true, process form data
        if($success)
        {
            unset($_SESSION['contacterror']);

            // set gate-keeper
            $okay = true;

            // retrieve data
            $first_name = (isset($_REQUEST['first_name'])) ?  filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
            $last_name = (isset($_REQUEST['last_name'])) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
            $telephone = (isset($_REQUEST['telephone'])) ?  filter_var($_REQUEST['telephone'], FILTER_SANITIZE_NUMBER_INT) : '';
            $email = (isset($_REQUEST['email'])) ?  filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
            $message = (isset($_REQUEST['message'])) ?  filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING) : '';

            // check for empty fields
            if($first_name === '' || $last_name === '' || $telephone === '' || $email === '' || $message === '')
            {
                $_SESSION['contacterror'] = "All fields are required";
                $okay = false;
                header("Location: /contact");
                exit();
            }

            if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
            {
                $_SESSION['contacterror'] = "Please enter valid email address";
                $okay = false;
                header("Location: /contact");
                exit();
            }

            // test
            // echo $first_name . "<br>";
            // echo $last_name . "<br>";
            // echo $telephone . "<br>";
            // echo $email . "<br>";
            // echo $message . "<br>";
            // exit();

            if($okay)
            {
                // call mailContactFormData method of Mail class & store boolean in $result
                $result = Mail::mailContactFormData($first_name, $last_name, $telephone, $email, $message);

                // if successful display success message in view
                if ($result)
                {
                    // display success message in view
                    $message = "Your information was sent. We will contact you as soon
                    as possible";

                    View::renderTemplate('Success/index.html', [
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                        'message'    => $message
                    ]);
                }
                else
                {
                    echo 'Mailer error';
                    exit();
                }
            }
        }
        else
        {
            $_SESSION['contacterror'] = "Please check reCAPTCHA box before submitting form.";
            header("Location: /contact");
            exit();
        }
    }




    public function updateContact()
    {
        // update contact page
        Page::updateContactPage();
    }
}
