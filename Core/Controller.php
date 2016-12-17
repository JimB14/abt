<?php

namespace Core;

// Abstract classes are special because they can never be instantiated.
// Instead, you typically inherit a set of base functionality from them in a
// new class. For that reason, they are commonly used as the base classes
// in a larger class hierarchy.
/**
 * Base controller
 *
 * PHP version 7.0
 */
abstract class Controller
{
    /**
     * Parameters from the matched route
     * @var array
     */
    protected $route_params = []; // to store route parameters

    /**
     * Class constructor
     *
     * @param  array $route_params Parameters from the route
     *
     * @return void
     */
    // pass parameters when object created
    public function __construct($route_params)
    {
        $this->route_params = $route_params;
    }



    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @return void
     */
    public function __call($name, $args) // name = method name
    {
        // add 'Action' suffix to method name
        $method = $name . 'Action';

        if(method_exists($this, $method)) // check if it exists
        {
            if($this->before() !== false)
            {
              call_user_func_array([$this, $method], $args); // call original method
              $this->after();
            }
        }
        else
        {
          //echo "Method $method not found in controller " . get_class($this);
          // backslash before Exception b/c it's a class
          throw new \Exception("Method $method not found in controller" .
                                get_class($this));
        }
    }


    /**
     * Before filter - called before an action method
     *
     * @return void
     */
    protected function before()
    {

    }


    /**
     * After filter - called after an action method
     *
     * @return void
     */
    protected function after()
    {

    }
}
