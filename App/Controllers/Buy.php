<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\Category;
use \App\Models\State;
use \App\Models\Listing;
use \App\Models\Broker;
use \App\Models\BrokerAgent;
use \App\Models\Subcategory;
use \App\Models\Counties;
use \App\Models\Contact;
use \App\Models\Realtylisting;
use \App\Mail;
use \App\Config;

use \Core\Model\PDO;

/**
 * Buy controller
 *
 * PHP version 7.0
 */
class Buy extends \Core\Controller
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
        // test
        // echo $_SERVER['DOCUMENT_ROOT'] . '<br><br>';
        // echo $_SERVER['SERVER_NAME']; exit();

        // get business categories to populate drop-down
        $categories = Category::getCategories();

        // get states to populate drop-down
        $states = State::getStates();

        // get featured listings
        $listings = Listing::getAllListings($limit = 25);

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';
        // exit();

        // get all listings
        $all_listings = Listing::getAllListings($limit = '0,100000');


        // test
        // echo '<pre>';
        // print_r($all_listings);
        // echo '</pre>';
        // exit();

        $toptitle = "Search Businesses";

        $subtitle = "Featured Listings";

        // store into variable
        // $results_html = View::getTemplate('Buy/results.html', [
        //     'categories'   => $categories,
        //     'states'       => $states,
        //     'listings'     => $listings, // data from Ajax request
        //     'toptitle'     => $toptitle,
        //     'subtitle'     => $subtitle,
        //     'all_listings' => $all_listings,
        //     'buyindex'     => 'active'
        // ]);

        // display view, pass arrays
        View::renderTemplate('Buy/index.html', [
            'categories'   => $categories,
            'states'       => $states,
            'listings'     => $listings,
            'toptitle'     => $toptitle,
            'subtitle'     => $subtitle,
            'all_listings' => $all_listings,
            'buyindex'     => 'active'
            //'results'      => $results_html
        ]);
    }


    /**
     * test
     */
    public function getAllListingsForLoadMore()
    {
        //sanitize post value ($page_number = 2 on 1st click)
        $page_number = filter_var($_POST['page'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);

        // set number of items to return on load more
        $items_per_page_count = 25;

        //get current starting point of records
        $offset = (($page_number-1) * $items_per_page_count);

        // test
        // echo '<br><br>$page_number = ' . $page_number . '<br>';
        // echo '$offset = ' . $offset. '<br>';
        // echo '$items_per_page = ' . $items_per_page. '<br>';
        // exit();

        //throw HTTP error if page number is not valid
        if(!is_numeric($page_number)){
          header('HTTP/1.1 500 Invalid page number!');
          exit();
        }

        // get listings (query identical with getAllListings except different params)
        $listings = Listing::getAllListingsForLoadMore($offset, $items_per_page_count);

        // test
        // echo '<pre>';
        // echo print_r($categories);
        // echo '</pre>';

        /**
         *  convert PHP to JSON & return to Ajax request
         *  (add - dataType: 'json', - to Ajax code)
         */
        // header('Content-Type: application/json');
        // echo json_encode($listings);

        View::renderTemplate('Buy/results.html', [
            'listings' => $listings
        ]);
    }





    /**
     * retrieves subctegories for selected category & sends to Ajax script
     *
     * @return json object The subcategories
     */
    public function getSubCategoriesAction()
    {
        // get subcategories
        $sub_categories = Subcategory::getSubCategories();

        // return JSON
        header('Content-Type: application/json');
        echo json_encode($sub_categories);
    }




    /**
     * retrieves counties of selected state & sends to Ajax script
     *
     * @return json object  The counties
     */
    public function getCountiesAction()
    {
        // get counties for selected state
        $counties = Counties::getCounties();

        // return JSON
        header('Content-Type: application/json');
        echo json_encode($counties);
    }




    /**
     * retrieves counties of selected state & sends to Ajax script
     *
     * @return json object  The counties
     */
    public function getCountyAction()
    {
        // get counties for selected state
        $counties = Counties::getCounty();

        // // return JSON
        header('Content-Type: application/json');
        echo json_encode($counties);
    }




    /**
     * gets image file name from array
     *
     * @return string Image file name
     */
    public function getImage()
    {
        // numeric array where index matches id
        $biz_category_images = [
            "accounting","advertising","agricultural","alcohol_related","amusement",
            "animals_pets","antiques","appliances","arts_crafts_floral",
            "automotive","aviation","awards_prizes","beauty_personal_care",
            "building_materials","business_services","camping","cards_gifts_books",
            "child_care","cleaning","clothing","communication","consignment_resale",
            "construction","consulting","convenience_stores","crafts_hobbies",
            "delivery","dental_related","distribution","educational_school",
            "electronics_computer","engineering","environment_related",
            "equipment_sales_service","financial","firearms","fitness",
            "flooring","flower_related","food_business","furniture","gas_station",
            "glass","hair_and_beauty","hardware","healthcare_medical",
            "hobby_related","ice_cream_yogurt_ice","import_export","industrial","insurance",
            "interior_design_decoration","internet","jewelry","lawn_landscaping",
            "legal", "liquor_related","locksmith","lodging","machine_shop","mail_order",
            "manufacturing","marine_related","marketing","medical_related",
            "metal_related","mining","miscellaneous_other","mobile_home",
            "motorcycle","moving","music","new_franchises","newspaper_magazine",
            "office_supplies","optical","pack_ship_postal","personal_services","personnel_services",
            "pest_control","photography","pool_and_spa","printing_typesetting",
            "professional_practices","publishing","real_estate","real_property_related",
            "recreation","rental_business","repair","restaurants","retail_miscellaneous",
            "routes","sales_and_marketing","security_related","services",
            "shoes_footwear","signs","sports_related","staffing_employment",
            "start_up_businesses","tailoring","technology","telecommunications",
            "telephone","toys","transportation","travel","upholstery_fabrics",
            "vending","video","water_related","wholesale"
        ];

        // make images array available
        //require '../Models/Library/biz-category-images.php';

        if(isset($_POST['id']))
        {

            $default_image = '';

            // Get selected category ID & store in variable
            $category_id = htmlspecialchars($_POST['id']);

            // loop thru numeric array of category images (index + 1 = category ID)
            foreach($biz_category_images as $key => $image)
            {
                if(($key + 1)  == $category_id){
                    $default_image = $image . '.jpg';
                }
            }
        }
        // return to Ajax request
        echo $default_image;
    }




    public function findBusinessesForSaleAction()
    {
        // set offset & count
        $offset = 0;
        $count = 300;

        // retrieve listing data from db per user criteria
        $results = Listing::findBusinessesBySearchCriteria($offset, $count);

        // create variables for search criteria
        $listings           = $results['listings'];
        $category_id        = $results['category_id'];
        $subcategory_id     = $results['subcategory_id'];
        $category_name      = $results['category_name'];
        $subcategory_name   = $results['subcategory_name'];
        $state              = $results['state'];
        $county             = $results['county'];

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';
        // exit();

        // get business categories to populate drop-down
        $categories = Category::getCategories();

        // get states to populate drop-down
        $states = State::getStates();

        $toptitle = "Find Your Business";

        $subtitle = "Search Results";

        // display listings in view; pass values
        View::renderTemplate('Buy/index.html', [
            'listings'          => $listings,
            'categories'        => $categories,
            'states'            => $states,
            'category_id'       => $category_id,
            'subcategory_id'    => $subcategory_id,
            'category_name'     => $category_name,
            'subcategory_name'  => $subcategory_name,
            'state'             => $state,
            'county'            => $county,
            'toptitle'          => $toptitle,
            'subtitle'          => $subtitle
        ]);
    }

// http://localhost/buy/find-businesses-for-sale?category=&subcategory=&state=OH&county=Trumbull
// http://localhost/buy/find-businesses-for-sale?category=all&subcategory=all&state=OH&county=Trumbull


    public function searchByKeywordAction()
    {
        // get listings from keyword search
        $results = Listing::findBusinessesByKeyword();

        $listings = $results['listings']; // assoc array
        $keywords = $results['keywords']; // assoc array

        // get business categories to populate drop-down
        $categories = Category::getCategories();

        // get states to populate drop-down
        $states = State::getStates();

        $toptitle = "Find Your Business";

        $subtitle = "Keyword Search Results";

        View::renderTemplate('Buy/index.html', [
            'listings'    => $listings,
            'categories'  => $categories,
            'states'      => $states,
            'keywords'    => $keywords,
            'toptitle'    => $toptitle,
            'subtitle'    => $subtitle
        ]);
    }



    public function viewListingDetailsAction()
    {
        // assign id to variable
        // $listing_id = $this->route_params['id'];
        // $broker_id = $this->route_params['broker_id'];
        // $listing_agent_id = $this->route_params['listing_agent_id'];

        // retrieve GET variables
        $listing_id = (isset($_REQUEST['listing_id'])) ? filter_var($_REQUEST['listing_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $agent_id = (isset($_REQUEST['listing_agent_id'])) ? filter_var($_REQUEST['listing_agent_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $limit = '';

        // test
        // echo $listing_id . "<br>";
        // echo $broker_id . "<br>";
        // echo $agent_id . "<br>";
        // exit();

        // get listing details from Listing model
        $listing = Listing::getListingDetails($listing_id);

        // assign img01 to variable
        $image = $listing->img01;


        // Assign image path to variable
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';

        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $image_path = Config::UPLOAD_PATH . '/assets/images/uploaded_business_photos/';
        }
        else
        {
          // path for local machine
          $image_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';
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

        // get all broker listings from Listing model
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
        View::renderTemplate('Buy/listing-details.html', [
            'listing'                   => $listing,
            'broker_listings'           => $broker_listings,
            'broker'                    => $broker,
            'agent'                     => $agent,
            'agent_listings'            => $agent_listings,
            'agent_id'                  => $agent_id,
            'img_width'                 => $img01_width,
            'agent_realty_listings'     => $agent_realty_listings,
            'broker_realty_listings'   => $broker_realty_listings
        ]);
    }




    public function agentProfileAction()
    {
      // get agent & broker IDs
      $agent_id = $this->route_params['id'];
      $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';
      $limit = '';

      // get agent data from BrokerAgent model
      $agent = BrokerAgent::getAgent($agent_id);

      // test
      // echo "<pre>";
      // print_r($agent);
      // echo "</pre>";
      // exit();

      // get comma separated string of states served
      $states_served = BrokerAgent::getStatesServed($agent_id);

      // get agent's listings from Listing model
      $agent_listings = Listing::getAllAgentListingsForProfilePage($broker_id, $agent_id, $limit);

      // $agent_listings = Listing::getAllAgentListings($agent_id, $limit);

      // test
      // echo '$agent_listings: <br>';
      // echo "<pre>";
      // print_r($agent_listings);
      // echo "</pre>";
      // exit();

      // get agent's real estate listings
      $agent_realty_listings = Realtylisting::getAgentListings($agent_id, $limit=6);

      // get all broker listings from Listing model
      $broker_listings = Listing::getListings($broker_id, $limit=8);

      // get broker real estate listings
      $broker_realty_listings = Realtylisting::getListings($broker_id, $id=null, $limit=8);

      // get listing broker data from Broker model
      $broker = Broker::getBrokerDetails($broker_id);

      // test
      // echo '<pre>';
      // print_r($agent);
      // echo '</pre>';
      // echo '<pre>';
      // print_r($broker);
      // echo '</pre>';
      // echo $states_served;

      // get agent businesses sold listings
      $agent_business_listings_sold = Listing::getListingsSold($broker_id, $agent_id, $limit=null);

      // store agent full name in variable
      $agent_full_name = $agent->first_name . ' ' . $agent->last_name;

      // display data in view
      View::renderTemplate('Buy/agent-profile.html', [
          'agent'                         => $agent,
          'states_served'                 => $states_served,
          'broker'                        => $broker,
          'agent_listings'                => $agent_listings,
          'agent_id'                      => $agent_id,
          'broker_listings'               => $broker_listings,
          'agent_realty_listings'         => $agent_realty_listings,
          'broker_realty_listings'        => $broker_realty_listings,
          'agent_full_name'               => $agent_full_name,
          'agent_business_listings_sold'  => $agent_business_listings_sold
      ]);

    }




    public function allBrokerListings()
    {
        // get broker ID from URL parameter
        $broker_id = filter_var($this->route_params['id'], FILTER_SANITIZE_NUMBER_INT);

        // get all listings for this broker
        $listings = Listing::getListings($broker_id, $limit = null);

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';

        // get listing broker data from Broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // broker status = 'sold' listings
        $broker_sold_listings = Listing::allBrokerSoldListings($broker_id);

        // test
        // echo '<pre>';
        // print_r($broker_sold_listings);
        // echo '</pre>';

        // get list of agents (team) from BrokerAgent model
        $agent_list = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo '<pre>';
        // print_r($agent_list);
        // echo '</pre>';

        // get broker real estate listings
        $broker_realty_listings = Realtylisting::getListings($broker_id, $id=null, $limit=null);

        // test
        // echo '<pre>';
        // print_r($broker_realty_listings);
        // echo '</pre>';

        // display in view
        View::renderTemplate('Buy/all-broker-listings.html', [
            'listings'                => $listings,
            'broker'                  => $broker,
            'broker_sold_listings'    => $broker_sold_listings,
            'agent_list'              => $agent_list,
            'broker_realty_listings'  => $broker_realty_listings
        ]);
    }




    public function contactBroker()
    {
        // retrieve variables
        $listing_id = (isset($_REQUEST['listing_id'])) ? filter_var($_REQUEST['listing_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $broker_id  = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $agent_id   = (isset($_REQUEST['agent_id'])) ? filter_var($_REQUEST['agent_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // test
        // echo $listing_id . '<br>';
        // echo $broker_id . '<br>';
        // echo $agent_id . '<br>';
        // exit();

        // get listing details
        $listing = Listing::getListingDetails($listing_id);

        // get broker details
        $broker = Broker::getBrokerDetails($broker_id);

        // get agent details
        $agent = BrokerAgent::getAgent($agent_id);

        // test
        // echo '<pre>';
        // print_r($agent);
        // echo '</pre>';
        // exit();

        // validate user data; return $results[] w/ form data
        $results = Contact::validateBrokerContactFormData();

        // test
        // echo '<pre>';
        // print_r($results);
        // echo '</pre>';
        // exit();

        // store data for email in array
        $listing_inquiry = [
            'id'              => $listing->clients_id,
            'ad_title'        => $listing->ad_title,
            'business_name'   => $listing->business_name,
            'agent_first_name'=> $agent->first_name,
            'agent_last_name' => $agent->last_name,
            'agent_email'     => $agent->agent_email,
            'company_name'    => $broker->company_name,
            'broker_email'    => $broker->broker_email,
            'first_name'      => $results['first_name'],
            'last_name'       => $results['last_name'],
            'telephone'       => $results['telephone'],
            'email'           => $results['email'],
            'investment'      => $results['investment'],
            'time_frame'      => $results['time_frame'],
            'message'         => $results['message']
        ];

        // test
        // echo '<pre>';
        // print_r($listing_inquiry);
        // echo '</pre>';
        // exit();

        // store lead data in array (field names match db.leads field names)
        $lead_data = [
          'listing_id'        => $id,
          'broker_id'         => $broker_id,
          'listing_agent_id'  => $agent_id,
          'clients_id'        => $listing->clients_id,
          'type'              => $listing->type,
          'ad_title'          => $listing->ad_title,
          'asking_price'      => $listing->asking_price,
          'address'           => $listing->address,
          'address2'          => $listing->address2,
          'city'              => $listing->city,
          'state'             => $listing->state,
          'county'            => $listing->county,
          'zip'               => $listing->zip,
          'description'       => $listing->description,
          'first_name'        => $results['first_name'],
          'last_name'         => $results['last_name'],
          'telephone'         => $results['telephone'],
          'email'             => $results['email'],
          'investment'        => $results['investment'],
          'time_frame'        => $results['time_frame'],
          'message'           => $results['message'],
          'agent_first_name'  => $agent->first_name,
          'agent_last_name'   => $agent->last_name,
        ];

        // test
        // echo '<pre>';
        // print_r($lead_data);
        // echo '</pre>';
        // exit();

        // send email to broker with user data
        $result = Mail::mailBrokerContactFormData($listing_inquiry);

        if($result)
        {
            // store lead data in `leads` table
            $result = Lead::setLeadData($lead_data);

            if($result)
            {
                $contact_msg1 = "Your information was sent.";
                $contact_msg2 = "You will be contacted as soon as possible.";
                $contact_msg3 = "Thank you for using American Biz Trader!";

                View::renderTemplate('Success/index.html', [
                    'first_name'      => $results['first_name'],
                    'last_name'       => $results['last_name'],
                    'contact_msg1'    => $contact_msg1,
                    'contact_msg2'    => $contact_msg2,
                    'contact_msg3'    => $contact_msg3,
                    'contact_broker'  => 'true'
                ]);
            }
            else
            {
                echo 'Error inserting lead data into database.';
                exit();
            }
        }
    }




    public function contactBrokerOnly()
    {
        // retrieve variables
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // test
        // echo $broker_id . '<br>';
        // exit();

        // get broker details
        $broker = Broker::getBrokerDetails($broker_id);

        // validate user data; return $results[] w/ form data
        $results = Contact::validateAgentOnlyContactFormData();

        // store data for email in array
        $listing_inquiry = [
            'company_name'  => $broker->company_name,
            'broker_email'  => $broker->broker_email,
            'first_name'    => $results['first_name'],
            'last_name'     => $results['last_name'],
            'telephone'     => $results['telephone'],
            'email'         => $results['email'],
            'message'       => $results['message']
        ];

        // send email to broker with user data
        $result = Mail::mailBrokerOnlyContactFormData($listing_inquiry);

        if($result)
        {
            $message = "Your information was sent. You will be contacted as
                soon as possible. Thank you for using American Biz Trader!";

            View::renderTemplate('Success/index.html', [
                'first_name' => $results['first_name'],
                'message'    => $message
            ]);
        }
    }




    public function contactAgentOnly()
    {
        // retrieve variables
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';
        $agent_id = (isset($_REQUEST['agent_id'])) ? filter_var($_REQUEST['agent_id'], FILTER_SANITIZE_NUMBER_INT): '';

        // test
        // echo $broker_id . '<br>';
        // echo $agent_id . '<br>';
        // exit();

        // get broker details
        $broker = Broker::getBrokerDetails($broker_id);

        // get agent details
        $agent = BrokerAgent::getAgent($agent_id);

        // validate user data; return $results[] w/ form data
        $results = Contact::validateAgentOnlyContactFormData();

        // store data for email in array
        $listing_inquiry = [
            'company_name'     => $broker->company_name,
            'broker_email'     => $broker->broker_email,
            'agent_email'      => $agent->agent_email,
            'agent_first_name' => $agent->first_name,
            'agent_last_name'  => $agent->last_name,
            'first_name'       => $results['first_name'],
            'last_name'        => $results['last_name'],
            'telephone'        => $results['telephone'],
            'email'            => $results['email'],
            'message'          => $results['message']
        ];

        // send email to broker with user data
        $result = Mail::mailAgentOnlyContactFormData($listing_inquiry);

        if($result)
        {
            $message = "Your information was sent. You will be contacted as
                soon as possible. Thank you for using American Biz Trader!";

            View::renderTemplate('Success/index.html', [
                'first_name' => $results['first_name'],
                'message'    => $message
            ]);
        }
    }

    /* - - - - - - - - - - Real estate listings - - - - - - - - - - - - - */

    public function findRealEstateForSaleAction()
    {
        // set offset & count
        $offset = 0;
        $count = 300;

        // retrieve listing data from db per user criteria
        $results = Realtylisting::findRealEstateBySearchCriteria($offset, $count);

        // create variables for search criteria
        $listings = $results['listings'];
        $type     = $results['type'];
        $subtype  = $results['subtype'];
        $state    = $results['state'];
        $county   = $results['county'];
        $zip      = $results['zip'];

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';
        // exit();

        // get states to populate drop-down
        $states = State::getStates();

        $toptitle = "Find Real Estate";

        $subtitle = "Search Results";

        // display listings in view; pass values
        View::renderTemplate('Realty/index.html', [
            'listings'  => $listings,
            'states'    => $states,
            'type'      => $type,
            'subtype'   => $subtype,
            'state'     => $state,
            'county'    => $county,
            'zip'       => $zip,
            'toptitle'  => $toptitle,
            'subtitle'  => $subtitle
        ]);
    }


}
