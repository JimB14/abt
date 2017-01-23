<?php

namespace App\Controllers\Admin;

use \Core\View;
use \App\Models\User;
use \App\Models\Broker;
use \App\Models\Listing;
use \App\Models\BrokerAgent;
use \App\Models\Category;
use \App\Models\State;
use \App\Models\Realtylisting;
use \App\Models\Paypal;
use \App\Models\Lead;
use \App\Models\Paypallog;

/**
 * Admin controller
 *
 * PHP version 7.0
 */
class Brokers extends \Core\Controller
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
     * Show the Admin Panel index page
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
            // get broker data from Broker model
            $broker = Broker::getBrokerByUserId($user_id);

            // get broker type & store in variable
            $broker_type = $broker->type;

            // test
            // echo $broker_type . '<br>';
            // echo '<pre>';
            // print_r($broker);
            // echo '</pre>';
            // exit();

            // store broker ID in session varible
            $_SESSION['broker_id'] = $broker->broker_id;

            // get agents id, last name, first name & broker ID only for drop-down
            $agents = BrokerAgent::getNamesOfAllBrokerAgents($_SESSION['broker_id'], $orderby = 'broker_agents.last_name');

            // test
            // echo '<pre>';
            // print_r($agents);
            // echo '</pre>';
            // exit();

            // render view & pass $broker object
            View::renderTemplate('Admin/index.html', [
                'broker'      => $broker,
                'agents'      => $agents,
                'broker_type' => $broker_type
            ]);
        }
    }




    public function myAccountAction()
    {
        // query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($_SESSION['broker_id'], $orderby = 'broker_agents.last_name');

        // get company name
        $company_name = Broker::getBrokerCompanyName($broker_id);

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // get user data
        $user = User::getUser($_SESSION['user_id']);

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

        // get paypal profile data from payflow gateway
        $ppProfile = Paypal::profileStatusInquiry($profileid);

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
            View::renderTemplate('Admin/Show/my-account.html', [
                'agents'        => $agents,
                'company_name'  => $company_name,
                'user'          => $user,
                'agent_count'   => $agent_count,
                'broker_type'   => $broker_type,
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

        // assign broker type to variable
        $broker_type = $broker->type;

        // get user data
        $user = User::getUser($broker->user_id);

        // test
        // echo "<pre>";
        // print_r($broker);
        // echo "</pre>";

        // get states for drop-down
        $states = State::getStates();

        // test
        // echo '<pre>';
        // print_r($broker);
        // echo '</pre>';
        // exit();

        // render view & pass $broker object
        View::renderTemplate('Admin/Show/company-profile.html', [
            'broker'      => $broker,
            'states'      => $states,
            'user'        => $user,
            'broker_type' => $broker_type
        ]);
    }




    public static function previewCompanyPage()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get all listings for this broker
        $listings = Listing::getListings($broker_id, $limit = null);

        // test
        // echo '<pre>';
        // print_r($listings);
        // echo '</pre>';

        // get listing broker data from Broker model
        $broker = Broker::getBrokerDetails($broker_id);

        // test
        // echo '<pre>';
        // print_r($broker);
        // echo '</pre>';

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

        // display in view
        View::renderTemplate('Buy/all-broker-listings.html', [
            'listings'              => $listings,
            'broker'                => $broker,
            'broker_sold_listings'  => $broker_sold_listings,
            'agent_list'            => $agent_list,
        ]);
    }



    /**
     * adds new agent to broker/company
     */
    public function addNewAgentAction()
    {
        // retrieve broker ID from query string
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get count of agents
        $number_agents = BrokerAgent::getCountOfAgents($broker_id);

        // get user record
        $user = User::getUser($_SESSION['user_id']);

        // store max_agents value in variable
        $max_agents = $user->max_agents;

        // test
        // echo '$max_agents: '. $max_agents . '<br>';
        // echo '$number_agents: ' . $number_agents;
        // exit();

        // check if current number of agents is less than agents paid for
        if($number_agents < $max_agents)
        {
            // get states array for drop-down
            $states = State::getStates();

            // get company type for menu display (broker type = business(1), realty(2), both(3))
            $broker_type = Broker::getBrokerCompanyType($broker_id);

            // render view
            View::renderTemplate('Admin/Add/add-new-agent.html', [
                'broker_id'     => $broker_id,
                'states'        => $states,
                'broker_type'   => $broker_type
            ]);
        }
        else
        {
            // user must pay for more agents

            // get user record
            $user = User::getUser($_SESSION['user_id']);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            // store user ID in variable
            $user_id = $user->id;

            // get user's PayPal data
            $profile = Paypallog::getPaypalData($user->id);

            // store user's PayPal profile ID in variable
            $profileid = $profile->PROFILEID;

            // create page title for payment view
            $pagetitle = "Add agents";

            // store explain data in variable
            $explain = 'Your credit card will be billed the "New monthly charge"
            amount on your next recurring payment date.';

            // render payment view & pass action for adding new agent
            View::renderTemplate('Paypal/index.html', [
                'user'      => $user,
                'pagetitle' => $pagetitle,
                'profileid' => $profileid,
                'explain'   => $explain,
                'add_agent' => 'true',
                'action'    => '/subscribe/process-payment-for-new-agents?id='.$user_id.'&profileid='.$profileid.'&maxagents='.$max_agents
            ]);
        }
    }


    /**
     * inserts new agent into broker_agents table
     *
     * @return boolean
     */
    public function postNewAgent()
    {
        // retrieve query string variables
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';
        $broker_user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // echo $broker_user_id; exit();

        // retrieve first and last name for quick db check
        $first_name = ( isset($_POST['first_name']) )? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = ( isset($_POST['last_name']) )? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING): '';

        // check if person already exists in broker_agents table
        $result = BrokerAgent::checkIfAgentAlreadyExists($first_name, $last_name, $broker_id);

        if($result)
        {
            $errorMessage = "Agent with the same first and last name already in system.";

            View::renderTemplate('Error/index.html', [
                'errorMessage' => $errorMessage
            ]);
            exit();
        }

        // check if profile photo included
        if(!empty($_FILES['profile_photo']['tmp_name']))
        {
            // add new agent (with photo) to broker_agents table
            $result = BrokerAgent::postNewAgent($broker_id, $broker_user_id);

            if($result)
            {
                // reduce `users`.`max_agents` by one
                $result = User::updateMaxagents($_SESSION['user_id']);

                if($result)
                {
                    $message = "Agent successfully added!";

                    // display success message
                    echo '<script>';
                    echo 'alert("'.$message.'")';
                    echo '</script>';

                    // redirect user to "Manage agents" page
                    echo '<script>';
                    echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
                    echo '</script>';
                    exit();
                }
                else
                {
                    echo "Error updating database.";
                    exit();
                }
            }
            else
            {
                echo "Error adding new agent to database.";
                exit();
            }
        }
        else
        {
            // add new agent (without) photo to broker_agents table
            $result = BrokerAgent::postNewAgentNoPhoto($broker_id, $broker_user_id);

            if($result)
            {
                // reduce `users`.`max_agents` by one
                $result = User::updateMaxagents($_SESSION['user_id']);

                if($result)
                {
                    $message = "Agent successfully added!";

                    // display success message
                    echo '<script>';
                    echo 'alert("'.$message.'")';
                    echo '</script>';

                    // redirect user to "Manage agents" page
                    echo '<script>';
                    echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
                    echo '</script>';
                    exit();
                }
                else
                {
                    echo "Error updating database.";
                    exit();
                }
            }
            else
            {
                echo "Error adding new agent to database.";
                exit();
            }
        }
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
        View::renderTemplate('Admin/Show/agents.html', [
            'agents'      => $agents,
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
            View::renderTemplate('Admin/Add/add-new-listing.html',[
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
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
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $listings = Listing::getListings($broker_id, $limit = null);

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
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'    => $listings,
            'agents'      => $agents,
            'broker_type' => $broker_type
        ]);
    }



    /**
     * retrieves listing record by broker ID
     *
     * @return view
     */
    public function showListingsById()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

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
        View::renderTemplate('Admin/Show/listings.html', [
            'listings' => $listings,
            'agents'   => $agents
        ]);
    }




    public function showListingsByAgentLastName()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $listings = Listing::getListingsByAgentLastName($broker_id);

        // test
        // echo "<pre>";
        // print_r($listings);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/listings.html', [
            'listings' => $listings,
            'agents'   => $agents
        ]);

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
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
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
        $results = Realtylisting::getRealtyListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

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
        View::renderTemplate('Admin/Show/realty-listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }



    public function searchLeadsByLastNameOrClientId()
    {
        // retrieve form data
        $broker_id   = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $input_value = ( isset($_REQUEST['last_name']) ) ?  filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING): '';
        $checkbox    = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

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
        $results = Lead::getLeadsBySearchCriteria($broker_id, $last_name, $clients_id, $limit=null);

        // test
        // echo "<pre>";
        // print_r($results);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        //$agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby = 'broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/leads.html', [
            'leads'       => $results['leads'],
            'pagetitle'   => $results['pagetitle'],
            // 'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }




    public function searchListingsByClientId()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // retrieve form data
        $clients_id = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';

        // display alert & redirect to same page on empty form submission
        if($clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id=' .$_SESSION['user_id'].'"';
            echo '</script>';
            exit();
        }

        // get listings and pagetitle
        $results = Listing::getListingsBySearchCriteria($broker_id, $last_name=null, $clients_id, $limit=null);

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
        View::renderTemplate('Admin/Show/listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }




    public function searchRealtyListingsByClientId()
    {
        // retrieve query string variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // retrieve form data
        $clients_id = ( isset($_REQUEST['clients_id']) ) ?  filter_var($_REQUEST['clients_id'], FILTER_SANITIZE_STRING): '';


        // display alert & redirect to same page on empty form submission
        if($clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id=' .$_SESSION['user_id'].'"';
            echo '</script>';
            exit();
        }


        // get listings and pagetitle
        $results = Realtylisting::getRealtyListingsBySearchCriteria($broker_id, $last_name=null, $clients_id, $limit=null);


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
        View::renderTemplate('Admin/Show/realty-listings.html', [
            'listings'    => $results['listings'],
            'pagetitle'   => $results['pagetitle'],
            'agents'      => $agents,
            'last_name'   => $last_name,
            'broker_type' => $broker_type
        ]);
    }





    public function editAgent()
    {
      // retrieve GET variable
      $agent_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
      $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

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

      View::renderTemplate('Admin/Update/edit-agent.html', [
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
            echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
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
            $errorMessage = "Error. An agent with listings cannot be deleted.
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
            $errorMessage = "Error. An agent with listings cannot be deleted.
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
            echo 'window.location.href="/admin/brokers/show-agents?id='.$broker_id.'"';
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
        View::renderTemplate('Admin/Show/agents.html', [
            'agents'      => $results['agents'],
            'pagetitle'   => $results['pagetitle'],
            'broker_id'   => $broker_id,
            'broker_type' => $broker_type
        ]);
    }




    public function updateCompanyProfile()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update company data in brokers table
        $result = Broker::updateCompanyProfile($broker_id);

        if($result)
        {
            $message = "Company profile successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. Company data not updated.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function updateCompanyBrokerPhoto()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // upload new image
        $result = Broker::updateCompanyBrokerPhoto($broker_id);

        if($result)
        {
            $message = "Broker photo successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. No file chosen.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function updateCompanyLogo()
    {
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // update company logo
        $result = Broker::updateCompanyLogo($broker_id);

        if($result)
        {
            $message = "Company logo successfully updated!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error. No file chosen.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/company-profile?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
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

        // get listing data
        $listing = Listing::getListingDetails($listing_id);

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

        View::renderTemplate('Admin/Update/edit-listing.html', [
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
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
            echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function  changePassword()
    {
        // retrieve variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // update password
        $result = User::updatePassword($user_id);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Your password was successfully changed! You must log back in.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/logout"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id='.$user_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function changeLoginEmail()
    {
        // retrieve variable
        $user_id = (isset($_REQUEST['user_id'])) ? filter_var($_REQUEST['user_id'], FILTER_SANITIZE_STRING) : '';

        // update password
        $result = User::updateLoginEmail($user_id);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Your login email was successfully changed! You must log back in.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/logout"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/index?user_id='.$user_id.'"';
            echo '</script>';
            exit();
        }
    }




    public function deleteListingImage()
    {
        // retrieve variable
        $listing_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';
        $image = (isset($_REQUEST['image'])) ? filter_var($_REQUEST['image'], FILTER_SANITIZE_STRING) : '';

        // delete from listing_images table
        $result = Listing::deleteListingImage($listing_id, $image);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Image successfully deleted.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            //echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo 'window.location.href="/admin/brokers/edit-listing?id='.$listing_id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user back to this page to see failed delete
            echo '<script>';
            echo 'window.location.href="/admin/brokers/edit-listing?id='.$listing_id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * inserts new real estate listing into database
     */
    public function addNewRealEstateListing()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

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
          View::renderTemplate('Admin/Add/add-new-real-estate-listing.html',[
              'agents'                => $agents,
              'states'                => $states,
              'broker_company_name'   => $broker_company_name,
              'broker_id'             => $broker_id,
              'broker_type'           => $broker_type
              // 'for_sale_categories'   => $for_sale_categories,
              // 'for_lease_categories'  => $for_lease_categories
          ]);
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
        View::renderTemplate('Admin/Show/realty-listings.html', [
            'listings'    => $listings,
            'agents'      => $agents,
            'broker_type' => $broker_type
        ]);
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
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }



    public function deleteRealEstateListing()
    {
        // retrieve ID from query string
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete listing from realty-listings table
        $result = Realtylisting::deleteRealEstateListing($id);

        // if listing successfully deleted
        if($result)
        {
            $message = "Real estate listing successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id='.$broker_id.'"';
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

        View::renderTemplate('Admin/Update/edit-real-estate-listing.html', [
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
     * updates data for specified record
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
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id='.$broker_id.'"';
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
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }


    /**
     * deletes specified real estate listing image for specified record by ID
     *
     * @return boolean
     */
    public function deleteRealEstateListingImage()
    {
        // retrieve variable
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $image = (isset($_REQUEST['image'])) ? filter_var($_REQUEST['image'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete from listing_images table
        $result = Realtylisting::deleteListingImage($id, $image);

        // display success alert and logout, or failure alert & redirect
        if($result)
        {
            $message = "Image successfully deleted.";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            //echo 'window.location.href="/admin/brokers/show-listings?id='.$broker_id.'"';
            echo 'window.location.href="/admin/brokers/edit-real-estate-listing?id='.$id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
        else
        {
            $message = "Error occurred. Please try again.";

            // display error message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user back to this page to see failed delete
            echo '<script>';
            echo 'window.location.href="/admin/brokers/edit-real-estate-listing?id='.$id.'&broker_id='.$broker_id.'"';
            echo '</script>';
            exit();
        }
    }



    public function showLeads()
    {
        // retrieve GET variable
        $broker_id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';

        // get listings
        $leads = Lead::getLeads($broker_id, $limit = null);

        // test
        // echo $_SESSION['broker_id'];
        // echo "<pre>";
        // print_r($leads);
        // echo "</pre>";
        // exit();

        // get agents id, last name, first name & broker ID only for drop-down
        $agents = BrokerAgent::getNamesOfAllBrokerAgents($broker_id, $orderby='broker_agents.last_name');

        // get company type (broker type = business(1), realty(2), both(3))
        $broker_type = Broker::getBrokerCompanyType($broker_id);

        $pagetitle = "My leads";

        // render view, pass $listings object
        View::renderTemplate('Admin/Show/leads.html', [
            'leads'       => $leads,
            'agents'      => $agents,
            'broker_type' => $broker_type
        ]);
    }



    public function deleteLead()
    {
        // retrieve GET variable
        $id = (isset($_REQUEST['id'])) ? filter_var($_REQUEST['id'], FILTER_SANITIZE_STRING) : '';
        $broker_id = (isset($_REQUEST['broker_id'])) ? filter_var($_REQUEST['broker_id'], FILTER_SANITIZE_STRING) : '';

        // delete lead from leads table
        $result = Lead::deleteLead($id, $broker_id);

        // if lead successfully deleted
        if($result)
        {
            $message = "Lead successfully deleted!";

            // display success message
            echo '<script>';
            echo 'alert("'.$message.'")';
            echo '</script>';

            // redirect user to "Manage agents" page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-leads?id='.$broker_id.'"';
            echo '</script>';
            exit();

            // optional php redirect - no msg
            // header("Location: /admin/brokers/show-agents?id=$broker_id");
            // exit();
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


}
