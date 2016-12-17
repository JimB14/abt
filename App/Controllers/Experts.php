<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\State;
use \App\Models\BrokerAgent;


/**
 * Experts controller
 *
 * PHP version 7.0
 */
class Experts extends \Core\Controller
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
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        // get states for drop-down
        $states = State::getStates();

        // get broker agents for all brokers
        $agents = BrokerAgent::getAllBrokerAgents($limit=20, $broker_id=null, $orderby='broker_agents.regDate DESC');

        // test
        // echo '<pre>';
        // print_r($agents);
        // echo '</pre>';
        // exit();

        $pagetitle = "Featured Experts";

        View::renderTemplate('Experts/index.html', [
            'states'      => $states,
            'agents'      => $agents,
            'pagetitle'   => $pagetitle
        ]);
    }

}
