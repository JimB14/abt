<?php

namespace App\Controllers;

use \App\Models\BrokerAgent;
use \Core\View;
use \App\Models\State;

/**
 * Search controller
 *
 * PHP version 7.0
 */
class Search extends \Core\Controller
{
    public function findExpert()
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
        // print_r($experts);
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
