<?php

namespace Core;

/**
 * Router
 *
 * PHP version 7.0
 */
class Router
{
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
     protected $params = [];

    /**
     * Add a route to the routing table
     *
     * @param string $route  The route URL
     * @param array  $params Parameters (controller, action, etc)
     *
     * @return void
     */
    public function add($route, $params = []) // params is an optional argument
    {
        // Convert the route {controller}/{action} to a regular expression

        // 1) find forward slash & replace with backslash & forward slash
        $route = preg_replace('/\//', '\\/', $route);

        // convert variable e.g. {controller} to regular expression
        // 2) find word between curly braces & replace with regular expression
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g.{id:\d+}
        // ([^\}]) means any character except a right curly brace
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }



    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }



    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found
     *
     * @param  string $url The route URL
     *
     * @return boolean   True if a match found, false otherwise
     */
    public function match($url)
    {
        /*
        foreach ($this->routes as $route => $params)
        {
            if ($url == $route)
            {
              // if match found, store $params in $params array
              $this->params = $params;
            }
            else
            {
                // set non-mapped routes to home/index
                $this->params = [
                  'controller' => 'home',
                  'action'     => 'index'
                ];
            }
        }
    }
    */

      /*
      // match to the fixe URL format controller/action
      $reg_exp = "/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/";

      if(preg_match($reg_exp, $url, $matches))
      {
          // get named capture group values
          $params = [];

          foreach($matches as $key => $match)
          {
              if(is_string($key))
              {
                  $params[$key] = $match;
              }
          }

          $this->params = $params;
          return true;
      }
      */


        foreach($this->routes as $route => $params)
        {
            // $route is a regular expression (not literal route)
            if(preg_match($route, $url, $matches))
            {
                foreach($matches as $key => $match)
                {
                    if(is_string($key))
                    {
                        $params[$key] = $match;
                    }
                }

                $this->params = $params;
                return true;
            }
        }
        return false;
    }


    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }


    /**
     * [dispatch description]
     *
     * @param  string $url The route URL
     *
     * @return void
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if($this->match($url))
        {
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            //$controller = "App\Controllers\\$controller"; // App\Controllers (namespace) where $controller class lives
            $controller = $this->getNamespace() . $controller;

            if(class_exists($controller))
            {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if(is_callable([$controller_object, $action]))
                {
                    $controller_object->$action();
                }
                else
                {
                    throw new \Exception("Method '$action' (in controller $controller)
                    not found");
                }
            }
            else
            {
                throw new \Exception("Controller class $controller not found");
            }
        }
        else
        {
            throw new \Exception('No route matched', 404);
        }
    }


    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param  string $string The string to convert
     *
     * @return string
     */
    public function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', '', $string)));
    }


    /**
     * Convert the string with hyphens to camelCase
     * e.g. add-new => addNew
     *
     * @param  string $string The string to convert
     *
     * @return string
     */
    public function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }


    /**
    * Remove the query string variables from the URL (if any). As the full
    * query string is used for the route, any variables at the end will need
    * to be removed before the route is matched to the routing table. For
    * example:
    *
    *   URL                           $_SERVER['QUERY_STRING']  Route
    *   -------------------------------------------------------------------
    *   localhost                     ''                        ''
    *   localhost/?                   ''                        ''
    *   localhost/?page=1             page=1                    ''
    *   localhost/posts?page=1        posts&page=1              posts
    *   localhost/posts/index         posts/index               posts/index
    *   localhost/posts/index?page=1  posts/index&page=1        posts/index
    *
    * A URL of the format localhost/?page (one variable name, no value) won't
    * work however. (NB. The .htaccess file converts the first ? to a & when
    * it's passed through to the $_SERVER variable).

     * @param  string $url The full URL
     *
     * @return string   The URL with the query string variables removed
     */
    protected function removeQueryStringVariables($url)
    {
        if($url != '')
        {
            $parts = explode('&', $url, 2); // & is converted ?

            if(strpos($parts[0], '=') === false)
            {
                $url = $parts[0];
            }
            else
            {
                $url = '';
            }
        }
        return $url;
    }



    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present
     *
     * @return string  The request URL
     */
    protected function getNamespace()
    {
        // default namespace name
        $namespace = 'App\Controllers\\';

        // Checks if the given key or index exists in the array (boolean)
        if(array_key_exists('namespace', $this->params))
        {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
