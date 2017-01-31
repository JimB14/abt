<?php

namespace App\Controllers;


use \Core\View;
use \App\Models\Testimonial;
use \App\Models\User;
use \App\Mail;

/**
 * Testimonials controller
 *
 * PHP version 7.0
 */
class Testimonials extends \Core\Controller
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


    /**
     * displays testimonials
     *
     * @return object The testimonials
     */
    public function indexAction()
    {
        $content = Testimonial::getAllTestimonials();

        View::renderTemplate('Testimonials/index.html', [
            'testimonials'      => $content,
            'testimonialsindex' => 'active'
        ]);
    }




    /**
     * shows add testimonials page
     */
    public function addTestimonial()
    {
        // get user data
        $user = User::getUser($_SESSION['user_id']);

        // render view
        View::renderTemplate('Testimonials/add-testimonial.html', [
            'user' => $user
        ]);
    }




    /**
     * processes submitted testimonial
     *
     * @return void
     */
    public function submitTestimonial()
    {
        //echo "Connected to submitTestimonial method in Testimonials controller!"; exit();

        // add testimonial to db
        $results = Testimonial::addNewTestimonial();

        // store $results array elements in variables
        $id = $results['id'];
        $token = $results['token'];
        $title = $results['title'];
        $testimonial = $results['testimonial'];
        $result = $results['result'];
        $user_id = $_SESSION['user_id'];

        // echo '<pre>';
        // print_r($results);
        // echo  '</pre>';
        // echo $result . "<br>";
        // echo $user_id . "<br>";
        // echo $_SESSION['user_id'] . "<br>";
        // exit();

        if($result)
        {
            // get $user object
            $user = User::getUser($_SESSION['user_id']);

            // store user_full_name in variable
            $user_full_name = $user->first_name . ' ' . $user->last_name;

            // send email to website owner or designee & pass testimonial data
            $result = Mail::sendNewTestimonialNotification($id, $user_id, $user_full_name, $token, $title, $testimonial);

            View::renderTemplate('Success/index.html', [
                'testimonialsuccess'  => 'true',
                'user'                => $user
            ]);
        }

        if(!$result)
        {
            echo "Error occurred with mailer sending testimony notification";
            exit();
        }
    }




    /**
     * Updates display field of testimonial
     *
     * @return boolean
     */
    public function publishTestimonial()
    {
        //echo "Connected to publishTestimonial method in testimonials controller!"; exit();

        // retrieve id & token from query string
        $id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT) : '';
        $token = isset($_REQUEST['token']) ? filter_var($_REQUEST['token'], FILTER_SANITIZE_STRING) : '';
        $user_id = isset($_REQUEST['user_id']) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // set display field to 1
        $result = Testimonial::setTestimonialToDisplay($id, $token);

        if($result)
        {
            // get $user object
            $user = User::getUser($user_id);
            $user_email = $user->email;
            $user_full_name = $user->first_name . ' ' . $user->last_name;

            // echo $user_email . '<br>';
            // echo $user_full_name . "<br>";
            // exit();

            // send thank you email
            $results = Mail::sendThanksForTestimonialEmail($user_email, $user_full_name);

            if($results)
            {
                header("Location: http://americanbiztrader.com/testimonials");
                exit();
            }
            else
            {
                echo "Error sending thank you email";
                exit();
            }
        }
    }
}
