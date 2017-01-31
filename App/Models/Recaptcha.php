<?php

namespace App\Models;


use \App\Config;


/**
 * Recaptcha model
 */
class Recaptcha extends \Core\Model
{
    /**
     * processes form submission thru Google ReCAPTCHA api
     *
     * @return object  The response from Google API
     */
    public static function recaptcha()
    {
      // store Google recaptcha values in variables
      $url        = Config::RECAPTCHAURL;
      $secret_key = Config::RECAPTCHASECRETKEY;

      // get response from Google API in JSON format
      $response = file_get_contents($url."?secret=".$secret_key."&response=".$_REQUEST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);

      // test
      // echo $response;

      // decode JSON response into object
      $data = json_decode($response);

      // return object to Controller
      return $data;
    }
}
