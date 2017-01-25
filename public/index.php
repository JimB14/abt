<?php
/**
 * Front controller
 *
 * PHP  version 7.0
 */

/**
 * Composer
 */
// loads class files; eliminates need to require files to use the class
require '../vendor/autoload.php';

// must come AFTER autoloader for classes to be known to SESSION variable
session_start();

// resource: http://stackoverflow.com/questions/520237/how-do-i-expire-a-php-session-after-30-minutes
// destroy session after 45 minutes of inactivity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 2700)) {

    // last request was more than 45 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp



/**
 * Twig (remove if running Twig 2.0)
 */
Twig_Autoloader::register();


/**
 * Error and Exception handling
 */
error_reporting(E_ALL); // — Sets which PHP errors are reported
// set_error_handler — Sets a user-defined error handler function
// call static errorHandler() method in Core/Error class
set_error_handler('Core\Error::errorHandler');
// set_exception_handler — Sets a user-defined exception handler function
// call static exceptionHandler() method in Core/Error class
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();

// Add routes (argument 1: route, argument 2: parameters (controller & action))
$router->add('', ['controller' => 'Home',  'action' => 'index']);
$router->add('buy-a-business', ['controller' => 'Buy', 'action' => 'index']);
$router->add('buy-lease-commercial-real-estate', ['controller' => 'Realty', 'action' => 'index']);

$router->add('terms-conditions', ['controller' => 'Terms', 'action' => 'index']);

$router->add('sell-a-business', ['controller' => 'Sell', 'action' => 'index']);
$router->add('find-real-estate', ['controller' => 'Home', 'action' => 'index']);
$router->add('find-a-business-expert', ['controller' => 'Experts', 'action' => 'index']);

$router->add('contact', ['controller' => 'Contact', 'action' => 'index']);
$router->add('testimonials', ['controller' => 'Testimonials', 'action' => 'index']);
$router->add('register', ['controller' => 'Register', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'index']);
$router->add('logout', ['controller' => 'Logout', 'action' => 'index']);

$router->add('login/login-user', ['controller' => 'Login', 'action' => 'login-user']);

$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']); // assign the namespace

$router->add('{controller}/{action}');
$router->add('{controller}/{id:\d+}/{action}'); // 'id' can be anything
$router->add('{controller}/{action}/{id:\d+}'); // controller, action and id can be in any order

$router->add('{controller}/{action}/{state}/{id:\d+}');

// call dispatch method of Router class
$router->dispatch($_SERVER['QUERY_STRING']);
