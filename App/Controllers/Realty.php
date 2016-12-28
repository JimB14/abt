<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\State;
use \App\Models\Listing;
use \App\Models\Broker;
use \App\Models\BrokerAgent;
use \App\Models\Counties;
use \App\Models\Contact;
use \App\Models\Realtylisting;
use \App\Mail;
use \App\Config;
use \Core\Model\PDO;

/**
 * Realty controller
 *
 * PHP version 7.0
 */
class Realty extends \Core\Controller
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
        // get featured listings
        $listings = Realtylisting::getRealtyListings($limit = 25);

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';
        // exit();

        // get all listings
        $all_listings = Realtylisting::getRealtyListings($limit = null);


        // get states to populate drop-down
        $states = State::getStates();

        $toptitle = "Search Real Estate Listings";
        $subtitle = "Featured Real Estate Listings";

        View::renderTemplate('Realty/index.html',[
            'states'                => $states,
            'realestateindex'       => 'active',
            'toptitle'              => $toptitle,
            'subtitle'              => $subtitle,
            'listings'              => $listings,
            'all_listings'          => $all_listings
        ]);
    }


    /**
     * displays listing details in view
     *
     * @return
     */
    public function viewListingDetailsAction()
    {
        // assign id to variable
        // $listing_id = $this->route_params['id'];
        // $broker_id = $this->route_params['broker_id'];
        // $listing_agent_id = $this->route_params['listing_agent_id'];

        // retrieve GET variables
        $id = (isset($_REQUEST['listing_id'])) ? filter_var($_REQUEST['listing_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $agent_id = (isset($_REQUEST['listing_agent_id'])) ? filter_var($_REQUEST['listing_agent_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $limit = '';

        // test
        // echo $listing_id . "<br>";
        // echo $broker_id . "<br>";
        // echo $agent_id . "<br>";
        // exit();

        // get listing & agent details from Realtylisting model
        $listing = Realtylisting::getRealEstateListing($id);

        // assign img01 to variable
        $image = $listing->img01;

        // Assign image path to variable
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_photos/';

        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $image_path = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_photos/';
        }
        else
        {
          // path for local machine
          $image_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_photos/';
        }

        $size = getimagesize($image_path . $image);

        // test
        // echo '<pre>';
        // print_r($size);
        // echo '</pre>';

        // store image width in variable
        $img01_width = $size[0];


        // test
        // echo "<pre>";
        // print_r($listing);
        // echo "</pre>";
        //exit();

        // get broker real estate listings from Listing model
        $broker_listings = Listing::getListings($broker_id, $limit = 10);

        // test
        // echo "<pre>";
        // print_r($broker_listings);
        // echo "</pre>";
        //exit();

        // get listing broker data from Broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // test
        // echo "<pre>";
        // print_r($broker);
        // echo "</pre>";
        //exit();

        // get listing agent data from BrokerAgent model
        $agent = BrokerAgent::getAgent($agent_id);

        // test
        // echo "<pre>";
        // print_r($agent);
        // echo "</pre>";
        //exit();

        // get agent's listings from Listing model
        $agent_listings = Listing::getAllAgentListings($agent_id, $limit=10);

        // test
        // echo '$agent_listings: <br>';
        // echo "<pre>";
        // print_r($agent_listings);
        // echo "</pre>";
        // exit();

        // get agent's real estate listings
        $agent_realty_listings = Realtylisting::getAgentListings($agent_id, $limit=10);

        // get broker real estate listings
        $broker_realty_listings = Realtylisting::getListings($broker_id, $id=null, $limit=6);

        // display results in view
        View::renderTemplate('Realty/listing-details.html', [
            'listing'                   => $listing,
            'broker_listings'           => $broker_listings,
            'broker'                    => $broker,
            'agent'                     => $agent,
            'agent_listings'            => $agent_listings,
            'agent_id'                  => $agent_id,
            'img_width'                 => $img01_width,
            'agent_realty_listings'     => $agent_realty_listings,
            'broker_realty_listings'    => $broker_realty_listings,
            'realtylistingdetailspage'  => 'active'
        ]);
    }



    public function searchRealEstateByKeywordAction()
    {
        // get listings from keyword search
        $results = Realtylisting::findRealEstateByKeyword();

        $listings = $results['listings']; // assoc array
        $keywords = $results['keywords']; // assoc array

        // get states to populate drop-down
        $states = State::getStates();

        $toptitle = "Find Real Estate";

        $subtitle = "Keyword Search Results";

        View::renderTemplate('Realty/index.html', [
            'listings'        => $listings,
            'states'          => $states,
            'keywords'        => $keywords,
            'toptitle'        => $toptitle,
            'subtitle'        => $subtitle,
            'realestateindex' => 'active'
            //'all_listings'    => $all_listings
        ]);
    }  

}
