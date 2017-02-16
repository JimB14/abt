<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\State;
use \App\Models\BrokerAgent;
use \App\Models\Listing;

/**
 * Home controller
 *
 * PHP version 7.0
 */
class Home extends \Core\Controller
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
        // echo "Hello from the index method in the Home controller!<br><br>"; exit();

        // get broker agents for all brokers
        $agents = BrokerAgent::getAllBrokerAgents($limit=5, $broker_id=null, $orderby='broker_agents.regDate DESC');

        // get featured listings
        $listings = Listing::getAllListings($limit = 5);

        $pagetitle = "Buy or Sell a Business with Confidence";

        $subtitle = "New Businesses for Sale";

        $populartitle = "Popular Categories";

        View::renderTemplate('Home/index.html', [
            'agents'        => $agents,
            'listings'      => $listings,
            'pagetitle'     => $pagetitle,
            'subtitle'      => $subtitle,
            'populartitle'  => $populartitle
        ]);
    }


}
