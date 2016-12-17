<?php

namespace Core;

/**
 * View
 *
 * PHP version 7.0
 */
class View
{
    /**
     * Render a vew files
     *
     * @param  string $view The view file
     *
     * @return void
     */
    public static function render($view, $args = [])
    {
        // convert associative array into individual variables
        // extract — Import variables into the current symbol table from an array
        // EXTR_SKIP: If there is a collision, don't overwrite the existing variable.
        extract($args, EXTR_SKIP);

        // store view into $file
        $file = "../App/Views/$view"; // relative to Core directory

        // check if file is readable
        if(is_readable($file))
        {
            require $file;
        }
        else
        {
            throw new \Exception("$file not found");
        }
    }


    /**
     * Render a view template using Twig
     *
     * @param  string $template The template file
     *
     * @param  array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        if($twig === null)
        {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);

            // to access $_SESSION['user'] ---> {{ session.user }}
            $twig->addGlobal('session', $_SESSION);

            // to access $_SERVER['HTTP_HOST'] ---> {{ server.http_host }}
            $twig->addGlobal('server', $_SERVER);

            // to access $_GET['key] ---> {{ get.variable }}
            $twig->addGlobal('get', $_GET);
        }
        echo $twig->render($template, $args);
    }


    /**
     * Gets the contents of a view template
     *
     * @param  string $template The template file
     *
     * @param  array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    public static function getTemplate($template, $args = [])
    {
        static $twig = null;

        if($twig === null)
        {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);

            // to access $_SESSION['user'] ---> {{ session.user }}
            $twig->addGlobal('session', $_SESSION);

            // to access $_SERVER['HTTP_HOST'] ---> {{ server.http_host }}
            $twig->addGlobal('server', $_SERVER);

            // to access $_GET['key] ---> {{ get.variable }}
            $twig->addGlobal('get', $_GET);
        }
        return $twig->render($template, $args);
    }
}
