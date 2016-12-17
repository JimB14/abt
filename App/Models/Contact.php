<?php

namespace App\Models;

use PDO;


class Contact extends \Core\Model
{
    public static function validateBrokerContactFormData()
    {
        // retrieve form data
        $first_name = (isset($_REQUEST['first_name'])) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $telephone = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING): '';
        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
        $investment = (isset($_REQUEST['investment'])) ? filter_var($_REQUEST['investment'], FILTER_SANITIZE_NUMBER_INT): '';
        $time_frame = (isset($_REQUEST['time_frame'])) ? filter_var($_REQUEST['time_frame'], FILTER_SANITIZE_STRING): '';
        $message = (isset($_REQUEST['message'])) ? filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING): '';

        // validate for data
        if($first_name == '' || $last_name == '' || $telphone = '' || $email == ''
        || $investment == '' || $time_frame == '' || $message == '')
        {
            echo "Error. All fields required.";
            exit();
        }

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';

        $listing_inquiry_form_data = [
          'first_name'  => $first_name,
          'last_name'   => $last_name,
          'telephone'   => $telephone,
          'email'       => $email,
          'investment'  => $investment,
          'time_frame'  => $time_frame,
          'message'     => $message
        ];

        return $listing_inquiry_form_data;
    }




    public static function validateBrokerOnlyContactFormData()
    {
        // retrieve form data
        $first_name = (isset($_REQUEST['first_name'])) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $telephone = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING): '';
        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
        $message = (isset($_REQUEST['message'])) ? filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING): '';

        // validate for data
        if($first_name == '' || $last_name == '' || $telphone = '' || $email == ''
        || $message == '')
        {
            echo "Error. All fields required.";
            exit();
        }

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';

        $broker_only_form_data = [
          'first_name'  => $first_name,
          'last_name'   => $last_name,
          'telephone'   => $telephone,
          'email'       => $email,
          'message'     => $message
        ];

        // return to Buy Controller
        return $broker_only_form_data;
    }




    public static function validateAgentOnlyContactFormData()
    {
        // retrieve form data
        $first_name = (isset($_REQUEST['first_name'])) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $telephone = (isset($_REQUEST['telephone'])) ? filter_var($_REQUEST['telephone'], FILTER_SANITIZE_STRING): '';
        $email = (isset($_REQUEST['email'])) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL): '';
        $message = (isset($_REQUEST['message'])) ? filter_var($_REQUEST['message'], FILTER_SANITIZE_STRING): '';

        // validate for data
        if($first_name == '' || $last_name == '' || $telphone = '' || $email == ''
        || $message == '')
        {
            echo "Error. All fields required.";
            exit();
        }

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';

        $agent_only_form_data = [
          'first_name'  => $first_name,
          'last_name'   => $last_name,
          'telephone'   => $telephone,
          'email'       => $email,
          'message'     => $message
        ];

        // return to Buy Controller
        return $agent_only_form_data;
    }
}
