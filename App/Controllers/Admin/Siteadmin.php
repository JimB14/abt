<?php

namespace App\Controllers\Admin;

use \App\Models\User;
use \Core\View;
use \App\Models\Broker;
use \App\Models\BrokerAgent;
use \App\Models\Paypallog;
use \App\Models\Paypal;
use \App\Models\State;
use \App\Models\Listing;
use \App\Models\Realtylisting;
use \App\Models\Category;


/**
 * Site Admin controller
 *
 * PHP version 7.0
 */
class Siteadmin extends \Core\Controller
{
    /**
     * Before filter
     *
     * @return void
     */
    protected function before()
    {
        //if SESSION is not set, send to login page
        if(!isset($_SESSION['user']))
        {
            header("Location: /login");
            exit();
        }
    }


    /**
     * Show the Site Admin Panel index page
     *
     * @return void
     */
    public function indexAction()
    {
        // retrieve GET variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // if user not logged in send to login page
        if(!$user_id)
        {
            header("Location: /login");
            exit();
        }
        else
        {
            // get user data from User model
            $user = User::getUser($user_id);

            // get brokers
            $brokers = Broker::getBrokers();

            // test
            // echo '<pre>';
            // print_r($user);
            // print_r($brokers);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Adminsite/index.html', [
                'user'    => $user,
                'brokers' => $brokers
            ]);
        }
    }



    public function getBrokerData()
    {
        // echo "Connected to getBrokerData() in Siteadmin Controller.";
        // exit();

        // get post data
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_NUMBER_INT): '';

        if($broker_id == '')
        {
            echo '<script>alert("Please select a company.")</script>';
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/index?user_id='.$_SESSION['user_id'].'"';
            echo '</script>';
            exit();
        }

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // store user ID in variable
        $user_id = $broker->user_id;

        // get user data by brokers.user_id
        $user = User::getUser($user_id);


        // test
        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';
        // exit();

        // render view & pass $broker object
        View::renderTemplate('Adminsite/index.html', [
            'brokers' => $brokers,
            'broker'  => $broker,
            'user'    => $user
          ]);
    }



    public function myAccountAction()
    {
        // query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        if($broker_id == '')
        {
            header("Location: /admin/siteadmin/index?user_id={{ session.user_id }}");
            exit();
        }

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // store user_id in variable
        $user_id = $broker->user_id;

        // get user data
        $user = User::getUser($user_id);

        // get number of agents
        $agent_count = BrokerAgent::getCountOfAgents($broker_id);

        // if max_agents > agent_count store value in variable (bill reduction)
        if($user->max_agents - $agent_count >= 1)
        {
            $extra_agents = $user->max_agents - $agent_count;
        }
        else
        {
            $extra_agents = '';
        }


        // get paypal_log data (LIMIT 1)
        $data = Paypallog::getTransactionData($user->id);

        // test
        // echo '<pre>';
        // print_r($data);
        // echo '</pre>';
        // echo $data->PROFILEID;
        // exit();

        if($data)
        {
            // store profileid in vaiable
            $profileid = $data->PROFILEID;
        }
        else
        {
            echo "Error occurred while fetching profileID.";
            exit();
        }

        if($profileid == 'courtesy')
        {
            echo "<h3>";
            echo 'This is a Courtesy account, compliments of American Biz
                  Trader.';
            echo '<br>';
            echo 'No account information available.';
            echo '<br><br>';
            echo '<button onclick="goBack()">';
            echo '<< Go back';
            echo '</button>';
            echo '<script>';
            echo 'function goBack()';
            echo '{window.history.go(-1);}';
            echo '</script>';
            exit();
        }


        // get paypal profile data from payflow gateway
        $ppProfile = Paypal::profileStatusInquiry($profileid);

        // test
        // echo '<pre>';
        // print_r($ppProfile);
        // echo '</pre>';
        // exit();

        if($ppProfile)
        {
            $last_four = substr($ppProfile['ACCT'], -4);

            // store RegExp values in variables
            $pattern     = '/(\d{2})(\d{2})(\d{4})/';
            $replacement = '\1-\2-\3';

            // store PayPal returned values in variables
            $creation_date = $ppProfile['CREATIONDATE'];
            $last_changed  = $ppProfile['LASTCHANGED'];
            $next_payment  = $ppProfile['NEXTPAYMENT'];

            // re-format (add hyphens) using RegExp for better readability
            $creation_date = preg_replace($pattern, $replacement, $creation_date);
            $last_changed  = preg_replace($pattern, $replacement, $last_changed);
            $next_payment  = preg_replace($pattern, $replacement, $next_payment);

            // test
            // echo $last_four . '<br>';
            // echo $creation_date . '<br>';
            // echo $last_changed . '<br>';
            // echo $next_payment . '<br>';
            // exit();

            // test
            // echo '<pre>';
            // print_r($ppProfile);
            // echo '</pre>';
            // exit();

            // get last transaction data
            //$results = Paypal::processPaymentHistory($profileid);

            // render view
            View::renderTemplate('Adminsite/Show/my-account.html', [
                'brokers'       => $brokers,
                'broker'        => $broker,
                'user'          => $user,
                'agent_count'   => $agent_count,
                'ppProfile'     => $ppProfile,
                'last_four'     => $last_four,
                'creation_date' => $creation_date,
                'last_changed'  => $last_changed,
                'next_payment'  => $next_payment,
                'extra_agents'  => $extra_agents
            ]);
        }
        else
        {
            echo "You appear to not have a payment profile.";
            exit();
        }
    }


    /**
     * retrieves company data
     *
     * @return [type] [description]
     */
    public static function companyProfileAction()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data from broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // assign broker type to variable
        $broker_type = $broker->type;

        // get states for drop-down
        $states = State::getStates();

        // get user data
        $user = User::getUser($broker->user_id);

        // test
        // echo '<pre>';
        // print_r($broker);
        // echo '</pre>';
        // exit();

        // render view & pass $broker object
        View::renderTemplate('Adminsite/Show/company-profile.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'user'        => $user,
            'states'      => $states,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * displays agents for specific broker
     *
     * @return Object  The broker's agents
     */
    public function showAgents()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data from broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get agent data
        $agents = BrokerAgent::getAllBrokerAgents($limit=null,$broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo "<pre>";
        // print_r($agents);
        // echo "</pre>";
        // exit();

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view & pass $agents object
        View::renderTemplate('Adminsite/Show/agents.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'agents'      => $agents,
            'broker_id'   => $broker_id,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * retrieves data for agent & renders view
     *
     * @return view
     */
    public function editAgent()
    {
      // retrieve GET variable
      $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
      $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

      // get broker data from broker model
      $broker = Broker::getBrokerDetails($broker_id);

      // get brokers for drop-down
      $brokers = Broker::getBrokers();

      // get agent data from BrokerAgent model
      $agent = BrokerAgent::getAgent($agent_id);

      // get states for drop-down
      $states = State::getStates();

      // get broker company name
      $broker_company_name = Broker::getBrokerCompanyName($broker_id);

      // test
      // echo "<pre>";
      // print_r($agent);
      // echo "</pre>";
      // exit();

      // get company type (broker type = business(1), realty(2), both(3))
      $broker_type = Broker::getBrokerCompanyType($broker_id);

      View::renderTemplate('Adminsite/Update/edit-agent.html', [
          'broker'        => $broker,
          'brokers'       => $brokers,
          'broker_id'     => $broker_id,
          'agent'         => $agent,
          'states'        => $states,
          'broker_type'   => $broker_type,
          'company_name'  => $broker_company_name
      ]);
    }




    public function deleteStateFromAgentProfile()
    {
        // retrieve GET variables
        $state = (isset($_REQUEST['state'])) ? filter_var($_REQUEST['state'], FILTER_SANITIZE_STRING) : '';
        $agent_id = (isset($_REQUEST['agent_id'])) ? filter_var($_REQUEST['agent_id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete state
        $result = Listing::deleteStateAndCounties($state, $agent_id, $broker_id);

        // send result back to Ajax method
        echo $result;
    }




    public function updateAgent()
    {
        // retrieve GET variables
        $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update agent record
        $result = BrokerAgent::updateAgent($agent_id, $broker_id);

        if($result)
        {
            $message = "Agent data successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-agents?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function deleteAgent()
    {
        // retrieve GET variables
        $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';


        // check for business listings for this agent
        $listings = Listing::getAllAgentListings($agent_id, $limit = null);

        // test
        // echo "<pre>";
        // echo print_r($listings);
        // echo "</pre>";
        // exit();

        if(!empty($listings))
        {
            $errorMessage = "Error. An agent with business listings cannot be deleted.
                Please re-assign listings to another agent before attempting to delete.";

            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // check for real estate listings for this agent
        $realty_listings = Realtylisting::getListingsByAgent($broker_id, $agent_id);

        // test
        // echo "<pre>";
        // echo print_r($realty_listings);
        // echo "</pre>";
        // exit();

        if(!empty($realty_listings))
        {
            $errorMessage = "Error. An agent with real estate listings cannot be deleted.
                Please re-assign listings to another agent before attempting to delete.";

            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // get profile photo file name
        $profile_photo = BrokerAgent::getProfilePhotoName($agent_id);

        // if profile photo exists delete from server
        if($profile_photo)
        {
            // remove profile photo from server
            $result = BrokerAgent::deleteProfilePhoto($profile_photo);
        }

        // delete agent from broker_agents table
        $result = BrokerAgent::deleteAgent($agent_id);

        // if agent successfully deleted
        if($result)
        {
            $message = "Agent successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-agents?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';
        }
    }




    public function searchAgents()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data from broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // retrieve form data
        $last_name = (isset($_REQUEST['last_name'])) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';

        // get agent data
        $results = BrokerAgent::getAgents($broker_id, $last_name);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view & pass $agents object
        View::renderTemplate('Adminsite/Show/agents.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'agents'      => $results['agents'],
            'pagetitle'   => $results['pagetitle'],
            'broker_id'   => $broker_id,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * retrieves broker data & renders view of form
     */
    public function addNewListing()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get broker company name
        $broker_company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get all agents
        // $agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');
        $agents = BrokerAgent::getAllBrokerAgentsByType($type=[1,3], $limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // get business categories
        $categories = Category::getCategories();

        // get states
        $states = State::getStates();

        // test
        // echo "<pre>";
        // print_r($states);
        // echo "</pre>";
        // exit();

        if(empty($agents))
        {
            $errorMessage = "Your company has no agents with a 'Type' that allows
            posting business listings. Please update the agent's profile. Go
            to Admin Panel > Agent / Broker > Manage agents / brokers, then click
            the 'Edit' button for the agent and modify 'Type' to the correct
            setting.";

            // render view
            View::renderTemplate('Error/index.html',[
              'errorMessage'  => $errorMessage
            ]);
            exit();
        }
        else
        {
            // render view
            View::renderTemplate('Adminsite/Add/add-new-listing.html',[
                'brokers'             => $brokers,
                'broker'              => $broker,
                'agents'              => $agents,
                'categories'          => $categories,
                'states'              => $states,
                'broker_company_name' => $broker_company_name,
                'broker_id'           => $broker_id,
                'broker_type'         => $broker_type
            ]);
        }
    }


    /**
     * inserts new listing into database
     *
     * @return view
     */
    public function postNewListing()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // post new listing
        $result = Listing::postNewListing($broker_id);

        if($result)
        {
            $message = "New listing successfully added!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * retrieve data for specified broker & render view
     *
     * @return view
     */
    public function showListings()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get listings
        $listings = Listing::getBusinessListingsForAdmin($broker_id, $limit = null);

        // test
        // echo $_SESSION['broker_id'];
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby='broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Adminsite/Show/listings.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'listings'    => $listings,
            'agents'      => $agents,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * retrieves listings record by broker ID
     *
     * @return view
     */
    public function showListingsById()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get listings
        $listings = Listing::getListingsById($broker_id);

        // test
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // render view, pass $listings object
        View::renderTemplate('Adminsite/Show/listings.html', [
            'brokers'  => $brokers,
            'broker'   => $broker,
            'listings' => $listings,
            'agents'   => $agents
        ]);
    }


    /**
     * retrieves listing record by listing ID & Broker ID
     *
     * @return view
     */
    public function editListing()
    {
        // retrieve GET variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get listing data
        $listing = Listing::getListingDetailsForAdmin($listing_id);

        // test
        // echo "<pre>";
        // echo print_r($listing);
        // echo "</pre>";
        // exit();

        // get all agents (for drop-down)
        $agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo "<pre>";
        // echo print_r($agents);
        // echo "</pre>";
        // exit();

        // get business categories (for drop-down)
        $categories = Category::getCategories();

        // get broker company name
        $broker_company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get states for drop-down
        $states = State::getStates();

        // test
        // echo "<pre>";
        // print_r($agent);
        // echo "</pre>";
        // exit();

        View::renderTemplate('Adminsite/Update/edit-listing.html', [
            'brokers'             => $brokers,
            'broker'              => $broker,
            'listing'             => $listing,
            'agents'              => $agents,
            'categories'          => $categories,
            'broker_company_name' => $broker_company_name,
            'states'              => $states,
            'broker_type'         => $broker_type
        ]);
    }


    /**
     * update listing record in database
     *
     * @return boolean
     */
    public function updateListing()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update listing
        $result = Listing::updateListing($listing_id, $broker_id);

        if($result)
        {
            $message = "Listing successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Unable to update listing.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * updates images for specified listing
     *
     * @return boolean
     */
    public function editListingImages()
    {
        // retrieve variables - listing ID & broker ID
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update images
        $result = Listing::updateListingImages($id, $broker_id);

        if($result)
        {
            $message = "Listing images successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user back to this edit page
            echo '<script>';
            // echo 'window.location.href="/admin/brokers/edit-listing?id='.$id.'&broker_id='.$broker_id.'"';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Unable to update listing images.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * deletes listing by ID
     *
     * @return boolean
     */
    public function deleteListing()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete listing from listing (cascades to listing_financial & listing_images)
        $result = Listing::deleteListing($listing_id);

        if($result)
        {
            $message = "Business listing successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Listing not deleted.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }

    /**
     * retrieves listings by agent last name or client ID
     *
     * @return view
     */
    public function searchListingsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id   = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox    = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // test
        // echo "Form & URL values:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'input value: ' . $input_value . '<br>';
        // echo 'checkbox: ' . $checkbox . '<br><br>';
        // exit();

        // if checkbox is checked, user searching by listing ID
        if($checkbox)
        {
            // assign input value to $client_id & make $last_name = null
            $clients_id = $input_value;
            $last_name = null;
            // echo "If checkbox is checked<br>";
            // echo 'client_id / field input: ' . $clients_id . '<br>';
            // echo 'last_name / should be null: ' . $last_name . '<br><br>';
            // exit();
        }
        if($checkbox == null)
        {
            $last_name = $input_value;
            $clients_id = null;
            // echo "If checkbox not checked<br>";
            // echo 'last_name: ' . $last_name . '<br>';
            // echo 'client_id / should be null: ' . $clients_id . '<br><br>';
            // exit();
        }

        // test
        // echo "After conditional statement:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'last_name / value if being searched: ' . $last_name . '<br>';
        // echo 'clients_id / value if being searched: ' . $clients_id . '<br>';
        // exit();

        // get listings and pagetitle
        $results = Listing::getListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Adminsite/Show/listings.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * inserts new real estate listing into database
     */
    public function addNewRealEstateListing()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get broker company name
        $broker_company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get all agents who are real estate brokers or business & real estate brokers
        //$agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');
        $agents = BrokerAgent::getAllBrokerAgentsByType($type=[2,3], $limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // get states
        $states = State::getStates();

        // for sale categories array
        // $for_sale_categories = [
        //     "All categories",
        //     "Multi-family",
        //     "Retail",
        //     "Industrial",
        //     "Office",
        //     "Income Business",
        //     "Entertainment",
        //     "Hospitality",
        //     "Medical",
        //     "Worship",
        //     "Land",
        //     "Other"
        // ];

        // for sale categories array
        // $for_lease_categories = [
        //     "All categories",
        //     "Retail",
        //     "Industrial",
        //     "Office",
        //     "Medical",
        //     "Short Term Office",
        //     "Other"
        // ];

        if(empty($agents))
        {
            $errorMessage = "Your company has no agents with a 'Type' that allows
            posting real estate listings. Please update the agent's profile. Go
            to Admin Panel > Agent / Broker > Manage agents / brokers, then click
            the 'Edit' button for the agent and modify 'Type' to the correct
            setting.";

            // render view
            View::renderTemplate('Error/index.html',[
              'errorMessage'  => $errorMessage
            ]);
            exit();
        }
        else
        {

          // render view
          View::renderTemplate('Adminsite/Add/add-new-real-estate-listing.html',[
              'brokers'             => $brokers,
              'broker'              => $broker,
              'agents'              => $agents,
              'states'              => $states,
              'broker_company_name' => $broker_company_name,
              'broker_id'           => $broker_id,
              'broker_type'         => $broker_type
              // 'for_sale_categories'   => $for_sale_categories,
              // 'for_lease_categories'  => $for_lease_categories
          ]);
        }
    }


    /**
     * inserts new real estate listing into database
     *
     * @return boolean
     */
    public function postNewRealEstateListing()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // post new listing
        $result = Realtylisting::postNewRealEstateListing($broker_id);

        if($result)
        {
            $message = "New real estate listing successfully added!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-real-estate-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }

    /**
     * retrieves listing record by ID & renders view
     *
     * @return view
     */
    public function showRealEstateListings()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get listings
        $listings = Realtylisting::getListings($broker_id, $id=null, $limit = null);

        // test
        // echo $_SESSION['broker_id'];
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents by type
        //$agents = BrokerAgent::getRealtyBrokerAgents($limit=null, $broker_id, $type=['2','3'], $orderby = 'broker_agents.last_name');
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby='broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Adminsite/Show/realty-listings.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'listings'    => $listings,
            'agents'      => $agents,
            'broker_type' => $broker_type
        ]);
    }


    /**
     * edit real estate listing in realty_listings table
     *
     * @return boolean
     */
    public function editRealEstateListing()
    {
        // retrieve GET variable from query string
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // get listing data
        $listing = Realtylisting::getRealEstateListing($id);

        // test
        // echo "<pre>";
        // print_r($listing);
        // echo "</pre>";
        // exit();

        // get states for drop-down
        $states = State::getStates();

        // get broker company name
        $broker_company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get agents for drop-down
        //$agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // get all agents who are real estate brokers or business & real estate brokers
        //$agents = BrokerAgent::getAllBrokerAgents($limit=null, $broker_id, $orderby = 'broker_agents.last_name');
        $agents = BrokerAgent::getAllBrokerAgentsByType($type=[2,3], $limit=null, $broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo "<pre>";
        // print_r($agents);
        // echo "</pre>";
        // exit();

        // for sale categories array
        $for_sale_categories = [
            "All categories",
            "Multi-family",
            "Retail",
            "Industrial",
            "Office",
            "Income Business",
            "Entertainment",
            "Hospitality",
            "Medical",
            "Worship",
            "Land",
            "Other"
        ];

        // for sale categories array
        $for_lease_categories = [
            "All categories",
            "Retail",
            "Industrial",
            "Office",
            "Medical",
            "Short Term Office",
            "Other"
        ];

        View::renderTemplate('Adminsite/Update/edit-real-estate-listing.html', [
            'brokers'               => $brokers,
            'broker'                => $broker,
            'listing'               => $listing,
            'states'                => $states,
            'agents'                => $agents,
            'for_sale_categories'   => $for_sale_categories,
            'for_lease_categories'  => $for_lease_categories,
            'broker_company_name'   => $broker_company_name,
            'broker_type'           => $broker_type
        ]);
    }


    /**
     * updates real estate listing record by ID
     *
     * @return boolean
     */
    public function updateRealEstateListing()
    {
        // retrieve get variable data from query string
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update listing
        $result = Realtylisting::updateRealEstateListing($id, $broker_id);

        if($result)
        {
            $message = "Real estate listing successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-real-estate-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Unable to update listing.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/siteadmin/show-real-estate-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * retrieves real estate listings by listing agent last name or client ID
     *
     * @return Object   The listings
     */
    public function searchRealtyListingsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // get broker data
        $broker = Broker::getBrokerDetails($broker_id);

        // get brokers for drop-down
        $brokers = Broker::getBrokers();

        // test
        // echo "Form & URL values:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'input value: ' . $input_value . '<br>';
        // echo 'checkbox: ' . $checkbox . '<br><br>';
        // exit();

        // if checkbox is checked, user searching by listing ID
        if($checkbox)
        {
            // assign input value to $client_id & make $last_name = null
            $clients_id = $input_value;
            $last_name = null;
            // echo "If checkbox is checked<br>";
            // echo 'client_id / field input: ' . $clients_id . '<br>';
            // echo 'last_name / should be null: ' . $last_name . '<br><br>';
            // exit();
        }
        if($checkbox == null)
        {
            $last_name = $input_value;
            $clients_id = null;
            // echo "If checkbox not checked<br>";
            // echo 'last_name: ' . $last_name . '<br>';
            // echo 'client_id / should be null: ' . $clients_id . '<br><br>';
            // exit();
        }

        // test
        // echo "After conditional statement:<br>";
        // echo 'broker_id: ' . $broker_id . '<br>';
        // echo 'last_name / value if being searched: ' . $last_name . '<br>';
        // echo 'clients_id / value if being searched: ' . $clients_id . '<br>';
        // exit();

        // get listings and pagetitle
        $results = Realtylisting::getRealtyListingsBySearchCriteriaForSiteAdmin($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results['listings']);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // test
        // echo "<pre>";
        // print_r($agents);
        // echo "</pre>";
        // exit();

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Adminsite/Show/realty-listings.html', [
            'brokers'     => $brokers,
            'broker'      => $broker,
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }


}
