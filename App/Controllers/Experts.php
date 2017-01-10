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


    public function findExpertAction()
    {
        // retrieve expert category, state, country values
        $expert_category = ( isset($_REQUEST['expert_category']) ) ? filter_var($_REQUEST['expert_category'], FILTER_SANITIZE_NUMBER_INT) : '';
        $state = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $county = ( isset($_REQUEST['county']) ) ? filter_var($_REQUEST['county'], FILTER_SANITIZE_STRING) : '';

        // get experts
        $agents = BrokerAgent::findExperts($expert_category, $state, $county, $orderby='broker_agents.state, broker_agents.last_name');

        // get states & pass to view for drop-down
        $states = State::getStates();

        // test
        // echo $expert_category . '<br>';
        // echo $state . '<br>';
        // echo $county . '<br>';
        //
        // echo '<pre>';
        // print_r($agents);
        // echo '</pre>';
        // exit();

        // set values based on expert category searched
        if($expert_category == 1)
        {
            $pagetitle = 'Business Brokers';
            $expert_type = 'Business Broker';
        }
        if($expert_category == 2)
        {
            $pagetitle = "Commercial Real Estate Brokers";
            $expert_type = 'Commercial Real Estate Broker';
        }
        if($expert_category == 3)
        {
            $pagetitle = "Business & Commercial Real Estate Brokers";
            $expert_type = 'Business & Commercial Real Estate Broker';
        }

        View::renderTemplate('Experts/index.html', [
            'agents'          => $agents,
            'states'          => $states,
            'pagetitle'       => $pagetitle,
            'statesearched'   => $state,
            'countysearched'  => $county,
            'expert_type'     => $expert_type
        ]);

    }

}
