<?php

namespace App\Models;

use PDO;
use \App\Config;


/**
 * Listing model
 */
class Listing extends \Core\Model
{

    /**
     * gets all listings
     *
     * @param  string $limit Sets limit to query
     * @return object        The listings
     */
    public static function getAllListings($limit)
    {
        if($limit != null)
        {
           $LIMIT = 'LIMIT ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            // get only fields used @Buy/index.html
            $sql = "SELECT listing.listing_id, listing.listing_agent_id, listing.ad_title, listing.clients_id,
                    listing.biz_description, listing.city, listing.hide_city, listing.state,
                    listing.county, listing.hide_county,
                    listing_images.img01, listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    listing_financial.asking_price, listing_financial.cash_flow,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    brokers.broker_id, brokers.type, brokers.company_name,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.type as agent_type,
                    broker_agents.agent_email
                    FROM listing
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    WHERE listing.display = '1'
                    ORDER BY create_date DESC
                    $LIMIT";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $listings;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * gets all listings
     *
     * @param  string $limit Sets limit to query
     * @return object        The listings
     */
    public static function getAllListingsForLoadMore($offset, $items_per_page_count)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // get only fields used @Buy/index.html
            $sql = "SELECT listing.listing_id, listing.listing_agent_id, listing.ad_title, listing.clients_id,
                    listing.biz_description, listing.city, listing.hide_city, listing.state,
                    listing.county, listing.hide_county,
                    listing_images.img01, listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    listing_financial.asking_price, listing_financial.cash_flow,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    brokers.broker_id, brokers.company_name,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email
                    FROM listing
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    WHERE listing.display = '1'
                    ORDER BY create_date DESC
                    LIMIT $offset, $items_per_page_count";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $listings;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    public static function findBusinessesBySearchCriteria($offset, $count)
    {
        // retrieve data from form
        $category    = ( isset($_REQUEST['category']) ) ? htmlspecialchars($_REQUEST['category']) : '';
        $subcategory = ( isset($_REQUEST['subcategory']) ) ? htmlspecialchars($_REQUEST['subcategory']) : '';
        $state       = ( isset($_REQUEST['state']) ) ? htmlspecialchars($_REQUEST['state']) : '';
        $county      = ( isset($_REQUEST['county']) ) ? htmlspecialchars($_REQUEST['county']) : '';

        // test
        // echo "Category ID: " . $category . '<br>';
        // echo "Subcategory ID: " . $subcategory . '<br>';
        // echo "State: " . $state . '<br>';
        // echo "Counties: " . $county . '<br><br>';
        // exit();

        /*  If user makes no particular selection that filter must be removed
         *  from query; but if used, included in query
         */

        // If no category is selected
        if ($category === "all")
        {
            $where_category = '';
        }
        else
        {
            // If category is selected
            $where_category = 'WHERE listing.category_id = :category_id';
        }

        // If no subcategory is selected
        if ($subcategory === 'all')
        {
            $where_subcategory = '';
        }
        else
        {
            // If subcategory is selected
            $where_subcategory = 'AND listing.subcategory_id = :subcategory_id';
        }

        // If no state selected
        if ($state === 'all')
        {
            $where_state = '';
        }
        else
        {
            // If state is selected
            $where_state = 'AND listing.state = :state';
        }

        // If no category selected and a state is selected
        if ($where_category === '' && $where_state != '')
        {
            $where_state = 'WHERE listing.state = :state';
            $where_category = '';
            $where_subcategory = '';
        }

        // If no county is selected
        if ($county === 'all' || $county === 'All counties') // || $county == '')
        {
            $where_county = '';
        }
        else
        {
            // If county is selected
            $where_county = 'AND listing.county = :county';
        }

        // If state and country is selected
        if ($where_category === '' && $where_subcategory === '' && $where_state != '' && $where_county != '')
        {
            $where_category = '';
            $where_subcategory = '';
            $where_state  = 'WHERE listing.state = :state';
            $where_county = 'AND listing.county = :county';
        }

        // test
        // echo '$where_category => ' . $where_category . '<br>';
        // echo '$where_subcategory => ' . $where_subcategory . '<br>';
        // echo '$where_state => ' . $where_state . '<br>';
        // echo '$where_county => ' . $where_county . '<br><br><br>';
        //exit();

        // test
        // echo '$category => ' . $category . '<br>';
        // echo '$subcategory => ' . $subcategory . '<br>';
        // echo '$state => ' . $state . '<br>';
        // echo '$county => ' . $county . '<br><br>';
        //exit();

        // Retrieve listing data from tables
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT
                    listing.listing_id, listing.listing_agent_id, listing.ad_title,
                    listing.biz_description, listing.county, listing.hide_county,
                    listing.state, listing.clients_id,
                    listing_financial.asking_price, listing_financial.cash_flow,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    listing_images.img01,
                    brokers.broker_id, brokers.company_name,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    category.name
                    FROM listing
                    INNER JOIN category
                    ON category.id = listing.category_id
                    INNER JOIN brokers
                    ON listing.broker_id = brokers.broker_id
                    INNER JOIN broker_agents
                    ON listing.listing_agent_id = broker_agents.id
                    INNER JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    $where_category
                    $where_subcategory
                    $where_state
                    $where_county
                    AND listing.display = '1'
                    ORDER BY create_date DESC
                    LIMIT $offset, $count";

            $stmt = $db->prepare($sql);

            if ($where_category != '')
            {
                $stmt->bindValue(':category_id', $category);
            }
            if ($where_subcategory != '')
            {
                $stmt->bindValue(':subcategory_id', $subcategory);
            }
            if ($where_state != '')
            {
                $stmt->bindValue(':state', $state);
            }               // bindValue if there is a value to bind
            if ($where_county != '')
            {
                $stmt->bindValue(':county', $county);
                          // bindValue if there is a value to bind
            }

            $stmt->execute();

            // Store results in array
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($category != '' and $category != 'all')
            {
                // get name of category from category ID
                $category_name = Category::getCategoryName($category);

                if($subcategory != '' and $subcategory != 'all')
                {
                  // get name of sub category from sub category ID
                  $subcategory_name = Category::getSubCategoryName($subcategory);
                }
                else
                {
                    $subcategory_name = 'Not searched';
                }
            }
            else
            {
                $category_name = 'Not searched';
                $subcategory_name = 'Not searched';
            }

            // create array to pass values back to Buy Controller
            $results = [
                'listings'         => $listings,
                'category_id'      => $category,
                'subcategory_id'   => $subcategory,
                'category_name'    => $category_name,
                'subcategory_name' => $subcategory_name,
                'state'            => $state,
                'county'           => $county
            ];

            // return $results to Buy Controller
            return $results;

        }
        catch (PDOException $e) {
            echo "Error fetching listing details from database" . $e->getMessage();
            exit();
        }
    }




    /**
     * retrieves listings with matched keywords
     *
     * @return array The matched listings
     */
    public static function findBusinessesByKeyword()
    {
        // Retrieve user data, sanitize and store in local variable
        $keywords = ( isset($_REQUEST['keywords']) )  ? filter_var(strtolower($_REQUEST['keywords']), FILTER_SANITIZE_STRING) : '';

        if($keywords === '' || filter_var($keywords, FILTER_SANITIZE_STRING === false))
        {
            echo '<h3>Error found. <br>You can search up to 3 comma
            separated keywords, (e.g. keyword1, keyword2, keyword3).<br>
            A keyword can contain more than one word, (e.g. keyword one, keyword
            two, keyword three).</h3>';
            exit();
        }

        // echo 'Stage 1: ' . $keywords . '<br>';

        /* Resource Tajinder Singh: http://stackoverflow.com/questions/4898800/php-regex-remove-space-after-every-comma-in-string  */
        while(strpos($keywords, ', ') != false)
        {
            $keywords = str_replace(', ', ',', $keywords);
        }

        // test
        // echo 'Stage 2: ' . $keywords . '<br>';
        // exit();

        // Explode string into array for use below
        $keyword_array = explode(",", $keywords);

        // test
        // echo '<pre>';
        // print_r($keyword_array);
        // echo '</pre>';
        // exit();

        // Get count of keywords that user is searching
        $keyword_count = count($keyword_array);


        // Create SQL query based on number of keywords being searched, throwing error >
        if(isset($keyword_count) && $keyword_count == 1)
        {
            $where_keywords = "WHERE (listing.keywords LIKE '%$keyword_array[0]%'
                                   OR listing.ad_title LIKE '%$keyword_array[0]%'
                                   OR listing.biz_description LIKE '%$keyword_array[0]%'
                                   OR listing.state LIKE '$keyword_array[0]%'
                                   )";
        }
        if(isset($keyword_count) && $keyword_count == 2)
        {
            $where_keywords = "WHERE (listing.keywords LIKE '%$keyword_array[0]%'
                                   OR listing.ad_title LIKE '%$keyword_array[0]%'
                                   OR listing.biz_description LIKE '%$keyword_array[0]%'
                                   OR listing.state LIKE '$keyword_array[0]%'
                                   OR listing.keywords LIKE '%$keyword_array[1]%'
                                   OR listing.ad_title LIKE '%$keyword_array[1]%'
                                   OR listing.biz_description LIKE '%$keyword_array[1]%'
                                   OR listing.state LIKE '$keyword_array[1]%'
                                   )";
        }
        if(isset($keyword_count) && $keyword_count == 3)
        {
            $where_keywords = "WHERE (listing.keywords LIKE '%$keyword_array[0]%'
                                   OR listing.ad_title LIKE '%$keyword_array[0]%'
                                   OR listing.biz_description LIKE '%$keyword_array[0]%'
                                   OR listing.state LIKE '$keyword_array[0]%'
                                   OR listing.keywords LIKE '%$keyword_array[1]%'
                                   OR listing.ad_title LIKE '%$keyword_array[1]%'
                                   OR listing.biz_description LIKE '%$keyword_array[1]%'
                                   OR listing.state LIKE '$keyword_array[1]%'
                                   OR listing.keywords LIKE '%$keyword_array[2]%'
                                   OR listing.ad_title LIKE '%$keyword_array[2]%'
                                   OR listing.biz_description LIKE '%$keyword_array[2]%'
                                   OR listing.state LIKE '$keyword_array[2]%'
                                   )";
        }
        if(isset($keyword_count) && $keyword_count > 3)
        {
            echo '<h2>Keyword search limit is three. Please try again searching up
            to 3 comma separated keywords.</h2>';
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.keywords, listing_images.img01, listing.listing_id,
                    listing.listing_agent_id, listing.ad_title, listing_financial.asking_price,
                    listing_financial.cash_flow, listing.biz_description,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    listing.city, listing.hide_city,listing.county, listing.hide_county,
                    listing.state, listing.clients_id, listing.create_date,
                    brokers.broker_id, brokers.company_name,
                    broker_agents.first_name, broker_agents.last_name,
                    category.name, sub_category.sub_cat_name
                    FROM listing
                    INNER JOIN category
                    ON category.id = listing.category_id
                    LEFT JOIN sub_category
                    ON sub_category.id = listing.subcategory_id
                    INNER JOIN brokers
                    ON listing.broker_id = brokers.broker_id
                    INNER JOIN broker_agents
                    ON listing.listing_agent_id = broker_agents.id
                    INNER JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    $where_keywords
                    AND listing.display = '1'
                    ORDER BY listing_financial.asking_price, listing.create_date DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            // Store results in array
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            $results = [
                'listings' => $listings,
                'keywords' => $keyword_array
            ];

            // return array to Buy Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    public static function getListingDetails($listing_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM listing
                    INNER JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    WHERE listing.listing_id = :listing_id
                    AND listing.display = '1'";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id' => $listing_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listing = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to controller
            return $listing;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }





    public static function getAllAgentListings($listing_agent_id, $limit)
    {
        if($limit != null)
        {
            $limit = "LIMIT $limit";
        }
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    WHERE listing.listing_agent_id = :listing_agent_id
                    AND listing.display = '1'
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_agent_id' => $listing_agent_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $agent_listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $agent_listings;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getAllAgentListingsForProfilePage($broker_id, $listing_agent_id, $limit)
    {
        if($limit != null)
        {
            $limit = "LIMIT $limit";
        }
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.listing_agent_id, listing.ad_title, listing.clients_id,
                    listing.biz_description, listing.city, listing.hide_city, listing.state,
                    listing.county, listing.hide_county,
                    listing_images.img01, listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    listing_financial.asking_price, listing_financial.cash_flow,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    brokers.broker_id, brokers.type, brokers.company_name,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.type as agent_type,
                    broker_agents.agent_email
                    FROM listing
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.listing_agent_id = :listing_agent_id
                    AND listing.listing_status = 'active'
                    ORDER BY create_date DESC
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id,
                ':listing_agent_id' => $listing_agent_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $agent_listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $agent_listings;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     *  gets listings where status = 'sold' by agent for specific broker
     *
     * @param  Int $broker_id   The broker's ID
     * @param  Int $agent_id    The agent's ID
     * @param  Int $limit       Count of records returned
     *
     * @return Object          The results / listings
     */
    public static function getListingsSold($broker_id, $agent_id, $limit=null)
    {
        if($limit != null)
        {
           $limit = 'LIMIT ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            // get only fields used @Buy/index.html
            $sql = "SELECT listing.listing_id, listing.listing_agent_id, listing.ad_title, listing.clients_id,
                    listing.biz_description, listing.city, listing.hide_city, listing.state,
                    listing.county, listing.hide_county, listing.listing_status,
                    listing_images.img01,
                    listing_financial.asking_price, listing_financial.cash_flow,
                    listing_financial.seller_financing_available,
                    listing_financial.lender_prequalified,
                    brokers.broker_id, brokers.type, brokers.company_name,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.type as agent_type,
                    broker_agents.agent_email
                    FROM listing
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.listing_agent_id = :listing_agent_id
                    AND listing.listing_status = 'sold'
                    ORDER BY create_date DESC
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'        => $broker_id,
                ':listing_agent_id' => $agent_id
            ];
            $stmt->execute($parameters);
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Controller
            return $listings;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }

    }





    public static function getListings($broker_id, $limit)
    {
        if($limit != null)
        {
          $limit = 'LIMIT  ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status, listing.business_name,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.hide_zip, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_for_sale, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,listing_financial.franchise,
                    listing_financial.home_based,listing_financial.relocatable,
                    listing_financial.lender_prequalified,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name, broker_agents.agent_email,
                    broker_agents.profile_photo,
                    brokers.broker_id, brokers.company_name
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.display = '1'
                    ORDER BY create_date DESC
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $listings;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getListingsById($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status, listing.business_name,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.hide_zip, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_for_sale, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,listing_financial.franchise,
                    listing_financial.home_based,listing_financial.relocatable,
                    listing_financial.lender_prequalified,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name, broker_agents.agent_email,
                    broker_agents.profile_photo,
                    brokers.broker_id, brokers.company_name
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.display = '1'
                    ORDER BY listing.clients_id";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $listings;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getListingsByAgentLastName($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status, listing.business_name,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.hide_zip, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_for_sale, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,listing_financial.franchise,
                    listing_financial.home_based,listing_financial.relocatable,
                    listing_financial.lender_prequalified,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name, broker_agents.agent_email,
                    broker_agents.profile_photo,
                    brokers.broker_id, brokers.company_name
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.display = '1'
                    ORDER BY broker_agents.last_name";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $listings;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit)
    {
        // if checkbox = false (not checked) & empty form is submitted
        if($clients_id == null && $last_name === '')
        {
            echo '<script>';
            echo 'alert("Please enter an agent last name.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id=' .$broker_id.'"';
            echo '</script>';
            exit();
        }

        // if checkbox = true (checked = on) & empty form submitted
        if($last_name == null && $clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a business listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-listings?id=' .$broker_id.'"';
            echo '</script>';
            exit();
        }

        if($limit != null)
        {
            $limit = 'LIMIT  ' . $limit;
        }
        if($last_name != null)
        {
            $last_name_for_view = $last_name;
            $last_name = "AND broker_agents.last_name LIKE '$last_name_for_view%'";
            $pagetitle = "Business listings by last name: $last_name_for_view";
        }
        if($clients_id != null)
        {
            $clients_id_for_view = $clients_id;
            $clients_id = "AND listing.clients_id LIKE '$clients_id_for_view'";
            $pagetitle = "Business listing by ID: $clients_id_for_view";
        }

        // execute query
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status, listing.business_name,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.hide_zip, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_for_sale, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,listing_financial.franchise,
                    listing_financial.home_based,listing_financial.relocatable,
                    listing_financial.lender_prequalified,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name, broker_agents.agent_email,
                    broker_agents.profile_photo,
                    brokers.broker_id, brokers.company_name
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    WHERE listing.broker_id = :broker_id
                    AND listing.display = '1'
                    $last_name
                    $clients_id
                    ORDER BY broker_agents.last_name
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // store in associative array
            $results = [
                'listings'  => $listings,
                'pagetitle' => $pagetitle
            ];

            // return associative array to Brokers Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function allBrokerSoldListings($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT listing.listing_id, listing.display, listing.broker_id,
                    listing.listing_agent_id, listing.category_id, listing.subcategory_id,
                    listing.clients_id, listing.ad_title, listing.listing_status,
                    listing.year_established, listing.number_of_employees, listing.country,
                    listing.county, listing.hide_county, listing.city, listing.hide_city,
                    listing.state, listing.zip, listing.biz_description, listing.square_feet,
                    listing.reason_selling, listing.growth_opportunities, listing.support,
                    listing.competition, listing.keywords, listing.biz_website,
                    listing.create_date, listing.last_update,
                    listing_financial.id, listing_financial.listing_id as listing_financial_id,
                    listing_financial.asking_price, listing_financial.gross_income,
                    listing_financial.cash_flow, listing_financial.ebitda,
                    listing_financial.inventory_included, listing_financial.inventory_value,
                    listing_financial.ffe_included, listing_financial.ffe_value,
                    listing_financial.real_estate_included, listing_financial.real_estate_value,
                    listing_financial.real_estate_description, listing_financial.seller_financing_available,
                    listing_financial.seller_financing_description,
                    listing_images.id as listing_images_id, listing_images.listing_id as listing_images_listing_id,
                    listing_images.broker_id as listing_images_broker_id, listing_images.img01,
                    listing_images.img02, listing_images.img03,
                    listing_images.img04, listing_images.img05, listing_images.img06,
                    broker_agents.id as agent_id, broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name, broker_agents.agent_email,
                    broker_agents.profile_photo,
                    brokers.broker_id, brokers.company_name
                    FROM listing
                    LEFT JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    LEFT JOIN listing_images
                    ON listing.listing_id = listing_images.listing_id
                    LEFT JOIN broker_agents
                    ON broker_agents.id = listing.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = listing.broker_id
                    WHERE listing.listing_status = :listing_status
                    AND listing.display = '1'
                    AND listing.broker_id = :broker_id
                    ORDER BY last_update DESC
                    LIMIT 10";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_status' => 'Sold',
                ':broker_id'      => $broker_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $broker_sold_listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to controller
            return $broker_sold_listings;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function deleteStateAndCounties($state, $agent_id, $broker_id)
    {
        // create variables ($state = 01, 02, 03, 04, 05)
        $state_serv = 'state_serv' . $state;
        $counties_serv = 'counties_serv' . $state;

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE broker_agents SET
                    $state_serv = null,
                    $counties_serv = null
                    WHERE id = :id
                    AND broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $agent_id,
                ':broker_id' => $broker_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function postNewListing($broker_id)
    {
        // echo "Connected to postNewListing method in Listing model!<br><br>";
        // echo '$broker_id from postNewListing method in Listing model: ' . $broker_id . "<br><br>";
        // exit();

        // Retrieve post data, sanitize and store in local variables
        $listing_agent_id = ( isset($_POST['listing_agent_id']) ) ? filter_var($_POST['listing_agent_id'], FILTER_SANITIZE_STRING) : '';
        $category_id = ( isset($_POST['category']) ) ? filter_var($_POST['category'], FILTER_SANITIZE_STRING) : '';
        $subcategory_id = ( isset($_POST['subcategory']) ) ? filter_var($_POST['subcategory'], FILTER_SANITIZE_STRING) : '';
        $clients_id = ( isset($_POST['clients_id']) ) ? filter_var($_POST['clients_id'], FILTER_SANITIZE_STRING) : '';
        $business_name = ( isset($_POST['business_name']) ) ? filter_var($_POST['business_name'], FILTER_SANITIZE_STRING) : '';
        $ad_title = ( isset($_POST['ad_title']) ) ? filter_var($_POST['ad_title'], FILTER_SANITIZE_STRING) : '';

        $listing_status = ( isset($_POST['listing_status']) ) ? filter_var($_POST['listing_status'], FILTER_SANITIZE_STRING) : '';
        $year_established = ( isset($_POST['year_established']) ) ? filter_var($_POST['year_established'], FILTER_SANITIZE_STRING) : '';
        $number_of_employees = ( isset($_POST['number_of_employees']) ) ? filter_var($_POST['number_of_employees'], FILTER_SANITIZE_STRING) : '';
        $country = ( isset($_POST['country']) ) ? filter_var($_POST['country'], FILTER_SANITIZE_STRING) : '';
        $county = ( isset($_POST['county']) ) ? filter_var($_POST['county'], FILTER_SANITIZE_STRING) : '';
        $hide_county = ( isset($_POST['hide_county']) ) ? filter_var($_POST['hide_county'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $hide_city = ( isset($_POST['hide_city']) ) ? filter_var($_POST['hide_city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $hide_zip = ( isset($_POST['hide_zip']) ) ? filter_var($_POST['hide_zip'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';

        $biz_description = ( isset($_POST['biz_description']) ) ? $_POST['biz_description'] : '';

        $square_feet = ( isset($_POST['square_feet']) ) ? filter_var($_POST['square_feet'], FILTER_SANITIZE_STRING) : '';
        $reason_selling = ( isset($_POST['reason_selling']) ) ? filter_var($_POST['reason_selling'], FILTER_SANITIZE_STRING) : '';
        $growth_opportunities = ( isset($_POST['growth_opportunities']) ) ? filter_var($_POST['growth_opportunities'], FILTER_SANITIZE_STRING) : '';
        $support = ( isset($_POST['support']) ) ? filter_var($_POST['support'], FILTER_SANITIZE_STRING) : '';
        $competition = ( isset($_POST['competition']) ) ? filter_var($_POST['competition'], FILTER_SANITIZE_STRING) : '';
        $keywords = ( isset($_POST['keywords']) ) ? strtolower(filter_var($_POST['keywords'], FILTER_SANITIZE_STRING)) : '';
        $biz_website = ( isset($_POST['keywords']) ) ? filter_var($_POST['biz_website'], FILTER_SANITIZE_STRING) : '';


        $asking_price = ( isset($_POST['asking_price']) ) ? filter_var($_POST['asking_price'], FILTER_SANITIZE_NUMBER_INT) : '';
        $gross_income = ( isset($_POST['gross_income']) ) ? filter_var($_POST['gross_income'], FILTER_SANITIZE_NUMBER_INT) : '';
        $cash_flow = ( isset($_POST['cash_flow']) ) ? filter_var($_POST['cash_flow'], FILTER_SANITIZE_NUMBER_INT) : '';
        $ebitda = ( isset($_POST['ebitda']) ) ? filter_var($_POST['ebitda'], FILTER_SANITIZE_NUMBER_INT) : '';
        $inventory_included = ( isset($_POST['inventory_included']) ) ? filter_var($_POST['inventory_included'], FILTER_SANITIZE_STRING) : '';
        $inventory_value = ( isset($_POST['inventory_value']) ) ? filter_var($_POST['inventory_value'], FILTER_SANITIZE_STRING) : '';
        $ffe_included = ( isset($_POST['ffe_included']) ) ? filter_var($_POST['ffe_included'], FILTER_SANITIZE_STRING) : '';
        $ffe_value = ( isset($_POST['ffe_value']) ) ? filter_var($_POST['ffe_value'], FILTER_SANITIZE_NUMBER_INT) : '';
        $ffe_description = ( isset($_POST['ffe_description']) ) ? filter_var($_POST['ffe_description'], FILTER_SANITIZE_STRING) : '';
        $real_estate_included = ( isset($_POST['real_estate_included']) ) ? filter_var($_POST['real_estate_included'], FILTER_SANITIZE_STRING) : '';
        $real_estate_for_sale = ( isset($_POST['real_estate_for_sale']) ) ? filter_var($_POST['real_estate_for_sale'], FILTER_SANITIZE_STRING) : '';
        $real_estate_value = ( isset($_POST['real_estate_value']) ) ? filter_var($_POST['real_estate_value'], FILTER_SANITIZE_NUMBER_INT) : '';
        $real_estate_description = ( isset($_POST['real_estate_description']) ) ? filter_var($_POST['real_estate_description'], FILTER_SANITIZE_STRING) : '';
        $seller_financing_available = ( isset($_POST['seller_financing_available']) ) ? filter_var($_POST['seller_financing_available'], FILTER_SANITIZE_STRING) : '';
        $seller_financing_description = ( isset($_POST['seller_financing_description']) ) ? filter_var($_POST['seller_financing_description'], FILTER_SANITIZE_STRING) : '';

        $franchise = ( isset($_POST['franchise']) ) ? filter_var($_POST['franchise'], FILTER_SANITIZE_NUMBER_INT) : '';
        $home_based = ( isset($_POST['home_based']) ) ? filter_var($_POST['home_based'], FILTER_SANITIZE_NUMBER_INT) : '';
        $relocatable = ( isset($_POST['relocatable']) ) ? filter_var($_POST['relocatable'], FILTER_SANITIZE_NUMBER_INT) : '';
        $lender_prequalified = ( isset($_POST['lender_prequalified']) ) ? filter_var($_POST['lender_prequalified'], FILTER_SANITIZE_NUMBER_INT) : '';

        //echo $hide_zip; exit();


        // Insert data for new listing into business table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO listing SET
                    broker_id             = :broker_id,
                    listing_agent_id      = :listing_agent_id,
                    category_id           = :category_id,
                    subcategory_id        = :subcategory_id,
                    clients_id            = :clients_id,
                    business_name         = :business_name,
                    ad_title              = :ad_title,
                    listing_status        = :listing_status,
                    year_established      = :year_established,
                    number_of_employees   = :number_of_employees,
                    country               = :country,
                    county                = :county,
                    hide_county           = :hide_county,
                    city                  = :city,
                    hide_city             = :hide_city,
                    state                 = :state,
                    hide_zip              = :hide_zip,
                    zip                   = :zip,
                    biz_description       = :biz_description,
                    square_feet           = :square_feet,
                    reason_selling        = :reason_selling,
                    growth_opportunities  = :growth_opportunities,
                    support               = :support,
                    competition           = :competition,
                    keywords              = :keywords,
                    biz_website           = :biz_website";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'            => $broker_id,
                ':listing_agent_id'     => $listing_agent_id,
                ':category_id'          => $category_id,
                ':subcategory_id'       => $subcategory_id,
                ':clients_id'           => $clients_id,
                ':business_name'        => $business_name,
                ':ad_title'             => $ad_title,
                ':listing_status'       => $listing_status,
                ':year_established'     => $year_established,
                ':number_of_employees'  => $number_of_employees,
                ':country'              => $country,
                ':county'               => $county,
                ':hide_county'          => $hide_county,
                ':city'                 => $city,
                ':hide_city'            => $hide_city,
                ':state'                => $state,
                ':hide_zip'             => $hide_zip,
                ':zip'                  => $zip,
                ':biz_description'      => $biz_description,
                ':square_feet'          => $square_feet,
                ':reason_selling'       => $reason_selling,
                ':growth_opportunities' => $growth_opportunities,
                ':support'              => $support,
                ':competition'          => $competition,
                ':keywords'             => $keywords,
                ':biz_website'          => $biz_website
            ];
            if( $stmt->execute($parameters) )
            {
                // Get business.id from this query for next query
                $listing_id = $db->lastInsertId();
            }
        }
        catch (PDOException $e)
        {
            echo "Error inserting data into database: " . $e->getMessage();
            exit();
        }


        // Insert data for new listing into financial table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO listing_financial SET
                    listing_id                    = :listing_id,
                    asking_price                  = :asking_price,
                    gross_income                  = :gross_income,
                    cash_flow                     = :cash_flow,
                    ebitda                        = :ebitda,
                    inventory_included            = :inventory_included,
                    inventory_value               = :inventory_value,
                    ffe_included                  = :ffe_included,
                    ffe_value                     = :ffe_value,
                    real_estate_included          = :real_estate_included,
                    real_estate_for_sale          = :real_estate_for_sale,
                    real_estate_value             = :real_estate_value,
                    real_estate_description       = :real_estate_description,
                    seller_financing_available    = :seller_financing_available,
                    seller_financing_description  = :seller_financing_description,
                    franchise                     = :franchise,
                    home_based                    = :home_based,
                    relocatable                   = :relocatable,
                    lender_prequalified           = :lender_prequalified";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id'                   => $listing_id,
                ':asking_price'                 => $asking_price,
                ':gross_income'                 => $gross_income,
                ':cash_flow'                    => $cash_flow,
                ':ebitda'                       => $ebitda,
                ':inventory_included'           => $inventory_included,
                ':inventory_value'              => $inventory_value,
                ':ffe_included'                 => $ffe_included,
                ':ffe_value'                    => $ffe_value,
                ':real_estate_included'         => $real_estate_included,
                ':real_estate_for_sale'         => $real_estate_for_sale,
                ':real_estate_value'            => $real_estate_value,
                ':real_estate_description'      => $real_estate_description,
                ':seller_financing_available'   => $seller_financing_available,
                ':seller_financing_description' => $seller_financing_description,
                ':franchise'                    => $franchise,
                ':home_based'                   => $home_based,
                ':relocatable'                  => $relocatable,
                ':lender_prequalified'          => $lender_prequalified
            ];
            $stmt->execute($parameters);
        }
        catch (PDOException $e)
        {
            echo "Error inserting listing data into database: " . $e->getMessage();
            exit();
        }


        // make images arrray available to script
        require 'Library/biz-category-images.php';

        // find category ID / image match in $biz_category_images images array
        if($category_id)
        {
            // loop thru numeric array of category images (index + 1 = category ID)
            foreach($biz_category_images as $key => $image)
            {
                if(($key + 1) == $category_id){
                    $default_image = $image . '.jpg';
                }
            }

            $img01 = $default_image;
        }
        else
        {
            // default image if above fails
            $img01 = 'hair_and_beauty.jpg';
        }


        /* - - - - - - - - - -  Upload default image to server  - - - - - - - - - */

        // Assign value to prefix for broker & listing-specific image identification
        $prefix = $broker_id.'-'.$listing_id.'-';

        // Resource: http://stackoverflow.com/questions/9748076/failed-to-open-stream-http-wrapper-does-not-support-writeable-connections

        // Path to image -- works only on local machine/server
        //$image_url = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/biz_categories/'.$img01;

        // Assign $image_url based on server
        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $image_url = Config::UPLOAD_PATH . '/assets/images/biz_categories/'.$img01;
        }
        else
        {
          // path for local machine
          $image_url = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/biz_categories/'.$img01;
        }

        // Open file to get existing content
        $data = file_get_contents($image_url);

        // Add prefix to filename
        $img01 = $prefix . $img01;

        // New file
        //$new = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/'.$img01;

        // Assign $new based on server
        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $new = Config::UPLOAD_PATH . '/assets/images/uploaded_business_photos/'.$img01;
        }
        else
        {
          // path for local machine
          $new = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/'.$img01;
        }

        // Write the contents back to a new file
        file_put_contents($new, $data);

        /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */


        // Assign target directory to variable
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';

        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_business_photos/';
        }
        else
        {
          // path for local machine
          $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';
        }


        /**
         * IMPORTANT - DO NOT DELETE CODE BELOW!
         * Functionality to upload multiple images removed per Dave 10/14/16
         * Retrieve array of posted images; implode array into comma separated string
         */
/*
        $images = implode(",", $_FILES['biz_photos']['name']);


        // Explode string at comma into array
        $image = explode(",", $images);


        // Store array elements into local variables, beginning with $img02 because $img01 is the default image
        $img02 = ( isset($image[0]) && $image[0] != '' ) ? htmlspecialchars($prefix . $image[0]) : '';
        $img03 = ( isset($image[1]) && $image[1] != '' ) ? htmlspecialchars($prefix . $image[1]) : '';
        $img04 = ( isset($image[2]) && $image[2] != '' ) ? htmlspecialchars($prefix . $image[2]) : '';
        $img05 = ( isset($image[3]) && $image[3] != '' ) ? htmlspecialchars($prefix . $image[3]) : '';
        $img06 = ( isset($image[4]) && $image[4] != '' ) ? htmlspecialchars($prefix . $image[4]) : '';
*/
        // Insert image paths into listing_images table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO listing_images
                    ( listing_id, broker_id, img01 )
                    VALUES
                    ( :listing_id, :broker_id, :img01)";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id' => $listing_id,
                ':broker_id'  => $broker_id,
                ':img01'      => $img01
            ];
            $stmt->execute($parameters);
        }
        catch (PDOException $e)
        {
            echo "Error inserting image paths into database: " . $e->getMessage();
            exit();
        }



        /**
         * IMPORTANT - DO NOT DELETE CODE BELOW!
         * Functionality to upload multiple images removed per Dave 10/14/16
         */
/*
        // Insert image paths into listing_images table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO listing_images
                    ( listing_id, broker_id, img01, img02, img03, img04, img05, img06 )
                    VALUES
                    ( :listing_id, :broker_id, :img01, :img02, :img03, :img04, :img05, :img06 )";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id' => $listing_id,
                ':broker_id'  => $broker_id,
                ':img01'      => $img01,
                ':img02'      => $img02,
                ':img03'      => $img03,
                ':img04'      => $img04,
                ':img05'      => $img05,
                ':img06'      => $img06
            ];
            $stmt->execute($parameters);
        }
        catch (PDOException $e)
        {
            echo "Error inserting image paths into database: " . $e->getMessage();
            exit();
        }
*/

/**
 * IMPORTANT - DO NOT DELETE CODE BELOW!
 * FUNCTIONALITY BELOW REMOVED FROM USER INTERFACE per Dave 10/14/16
*  USER CANNOT UPLOAD MULTIPLE IMAGES WHILE POSTING NEW LISTING
*  IMPORTANT! If activated, the code below must be modified to match the code
*  directly above. The file paths for each server must be configured.
*/


    /* - - - - - - - - - -  Upload images to server  - - - - -  - - - -  */

        /* Check if at least one image was selected to be uploaded; if yes, execute, if no, skip  */

/*        if($img02 != '')
        {
            foreach($_FILES['biz_photos']['tmp_name'] as $key => $tmp_name)
            {

                // Assign value to checker variable
                $upload_ok = 1;

                // Assign array element to new string variable
                $file_name = $_FILES['biz_photos']['name'][$key];
                $file_size = $_FILES['biz_photos']['size'][$key];
                $file_tmp = $_FILES['biz_photos']['tmp_name'][$key];
                $file_type = $_FILES['biz_photos']['type'][$key];
                $file_err_msg = $_FILES['biz_photos']['error'][$key];

                //echo $file_name; exit;

                // Separate file name into an array by the dot
                $kaboom = explode(".", $file_name);

                // Assign last element of array to file_extension variable (in case file has more than one dot)
                $file_extension = end($kaboom);


                /* - - - - -  Error handling  - - - - - */

                // Check if file  exists
                // if( file_exists($target_dir . $file_name) )
                // {
                //     $upload_ok = 0;
                //     echo nl2br('Sorry, image file already exists. <br> Please select a different file or rename file and try again.');
                //     exit();
                // }

                // Check if file size < 2 MB
/*                if($file_size > 2097152)
                {
                    $upload_ok = 0;
                    unlink($file_tmp);
                    echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                    exit();
                }

                // Check if file is gif, jpg, jpeg or png
                if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
                {
                    $upload_ok = 0;
                    unlink($file_tmp);
                    echo 'Image must be gif, jpg, jpeg, or png to upload.';
                    exit();
                }

                // Check for any errors
                if($file_err_msg == 1)
                {
                    $upload_ok = 0;
                    echo 'Error uploading file. Please try again.';
                    exit();
                }

                if( $upload_ok = 1 )
                {

                    // Attach prefix to file name so server & database table match
                    $image = $prefix . $file_name;


                    // Upload file to server into designated folder
                    $move_result = move_uploaded_file($file_tmp, $target_dir . $image);

                    if ($move_result != true)
                    {
                        unlink($file_tmp);
                        echo $file_name . ' not uploaded. Please try again.';
                        exit();
                    }


                    /*  - - - - - - - -  Image Re-sizing   - - - - - - - - - -  */

/*                    include_once 'Library/image-resizing-to-scale.php';
                    $target_file =  $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$image";
                    $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$image";
                    $wmax = 750;
                    $hmax = 750;
                    image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);

                    //$resized_image[$key] = $image;

                    //move_uploaded_file($tmp_name, "uploaded_files/{$_FILES['biz_photos']['name'][$key]}");

                }
            }
        }
*/
        return true;
    }




    public static function deleteListing($listing_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM listing WHERE listing_id = :listing_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id' => $listing_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateListing($listing_id, $broker_id)
    {
        // retrieve data
        $listing_agent_id = ( isset($_POST['listing_agent_id']) ) ? filter_var($_POST['listing_agent_id'], FILTER_SANITIZE_STRING) : '';
        $category_id = ( isset($_POST['category']) ) ? filter_var($_POST['category'], FILTER_SANITIZE_STRING) : '';
        $subcategory_id = ( isset($_POST['subcategory']) ) ? filter_var($_POST['subcategory'], FILTER_SANITIZE_STRING) : '';
        $clients_id = ( isset($_POST['clients_id']) ) ? filter_var($_POST['clients_id'], FILTER_SANITIZE_STRING) : '';
        $business_name = ( isset($_POST['business_name']) ) ? filter_var($_POST['business_name'], FILTER_SANITIZE_STRING) : '';
        $ad_title = ( isset($_POST['ad_title']) ) ? filter_var($_POST['ad_title'], FILTER_SANITIZE_STRING) : '';
        $listing_status = ( isset($_POST['listing_status']) ) ? filter_var($_POST['listing_status'], FILTER_SANITIZE_STRING) : '';
        $display = ( isset($_POST['display']) ) ? filter_var($_POST['display'], FILTER_SANITIZE_STRING) : '';
        $year_established = ( isset($_POST['year_established']) ) ? filter_var($_POST['year_established'], FILTER_SANITIZE_STRING) : '';
        $number_of_employees = ( isset($_POST['number_of_employees']) ) ? filter_var($_POST['number_of_employees'], FILTER_SANITIZE_STRING) : '';
        $country = ( isset($_POST['country']) ) ? filter_var($_POST['country'], FILTER_SANITIZE_STRING) : '';
        $county = ( isset($_POST['county']) ) ? filter_var($_POST['county'], FILTER_SANITIZE_STRING) : '';
        $hide_county = ( isset($_POST['hide_county']) ) ? filter_var($_POST['hide_county'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $hide_city = ( isset($_POST['hide_city']) ) ? filter_var($_POST['hide_city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $hide_zip = ( isset($_POST['hide_zip']) ) ? filter_var($_POST['hide_zip'], FILTER_SANITIZE_NUMBER_INT) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';

        $biz_description = ( isset($_POST['biz_description']) ) ? $_POST['biz_description'] : '';

        $square_feet = ( isset($_POST['square_feet']) ) ? filter_var($_POST['square_feet'], FILTER_SANITIZE_STRING) : '';
        $reason_selling = ( isset($_POST['reason_selling']) ) ? filter_var($_POST['reason_selling'], FILTER_SANITIZE_STRING) : '';
        $growth_opportunities = ( isset($_POST['growth_opportunities']) ) ? filter_var($_POST['growth_opportunities'], FILTER_SANITIZE_STRING) : '';
        $support = ( isset($_POST['support']) ) ? filter_var($_POST['support'], FILTER_SANITIZE_STRING) : '';
        $competition = ( isset($_POST['competition']) ) ? filter_var($_POST['competition'], FILTER_SANITIZE_STRING) : '';
        $keywords = ( isset($_POST['keywords']) ) ? filter_var($_POST['keywords'], FILTER_SANITIZE_STRING) : '';
        $biz_website = ( isset($_POST['biz_website']) ) ? filter_var($_POST['biz_website'], FILTER_SANITIZE_STRING) : '';


      // test
      // echo '(2) $_POST[\'listing_status\'] => ' . $_POST['listing_status'] . '<br>';
      // echo '(2) ' . $_POST['country'] . '<br>';
      // echo '(2) ' . $_POST['state'] . '<br><br>';
      //
      // echo '$listing_status => ' . $listing_status . '<br>';
      // echo '$country => ' . $country . '<br>';
      // echo '$state => ' . $state . '<br>';

        // Update data for listing in `listing` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE listing SET
                    listing_agent_id = :listing_agent_id,
                    category_id = :category_id,
                    subcategory_id = :subcategory_id,
                    clients_id = :clients_id,
                    business_name = :business_name,
                    ad_title = :ad_title,
                    listing_status = :listing_status,
                    display = :display,
                    year_established = :year_established,
                    number_of_employees = :number_of_employees,
                    country = :country,
                    county = :county,
                    hide_county = :hide_county,
                    city = :city,
                    hide_city = :hide_city,
                    state = :state,
                    hide_zip = :hide_zip,
                    zip = :zip,
                    biz_description = :biz_description,
                    square_feet = :square_feet,
                    reason_selling = :reason_selling,
                    growth_opportunities = :growth_opportunities,
                    support = :support,
                    competition = :competition,
                    keywords = :keywords,
                    biz_website = :biz_website
                    WHERE listing_id = :listing_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id'           => $listing_id,
                ':listing_agent_id'     => $listing_agent_id,
                ':category_id'          => $category_id,
                ':subcategory_id'       => $subcategory_id,
                ':clients_id'           => $clients_id,
                ':business_name'        => $business_name,
                ':ad_title'             => $ad_title,
                ':listing_status'       => $listing_status,
                ':display'              => $display,
                ':year_established'     => $year_established,
                ':number_of_employees'  => $number_of_employees,
                ':country'              => $country,
                ':county'               => $county,
                ':hide_county'          => $hide_county,
                ':city'                 => $city,
                ':hide_city'            => $hide_city,
                ':state'                => $state,
                ':hide_zip'             => $hide_zip,
                ':zip'                  => $zip,
                ':biz_description'      => $biz_description,
                ':square_feet'          => $square_feet,
                ':reason_selling'       => $reason_selling,
                ':growth_opportunities' => $growth_opportunities,
                ':support'              => $support,
                ':competition'          => $competition,
                ':keywords'             => $keywords,
                ':biz_website'          => $biz_website
              ];
            $stmt->execute($parameters);
        }
        catch (PDOException $e)
        {
            echo "Error updating listing data in database: " . $e->getMessage();
            exit();
        }


        /* - - - - - - - Update financial data  - - - - - - - */

        $asking_price = ( isset($_POST['asking_price']) ) ? filter_var($_POST['asking_price'], FILTER_SANITIZE_STRING) : '';
        $gross_income = ( isset($_POST['gross_income']) ) ? filter_var($_POST['gross_income'], FILTER_SANITIZE_STRING) : '';
        $cash_flow = ( isset($_POST['cash_flow']) ) ? filter_var($_POST['cash_flow'], FILTER_SANITIZE_STRING) : '';
        $ebitda = ( isset($_POST['ebitda']) ) ? filter_var($_POST['ebitda'], FILTER_SANITIZE_STRING) : '';
        $inventory_value = ( isset($_POST['inventory_value']) ) ? filter_var($_POST['inventory_value'], FILTER_SANITIZE_STRING) : '';
        $inventory_included = ( isset($_POST['inventory_included']) ) ? filter_var($_POST['inventory_included'], FILTER_SANITIZE_STRING) : '';
        $ffe_value = ( isset($_POST['ffe_value']) ) ? filter_var($_POST['ffe_value'], FILTER_SANITIZE_STRING) : '';
        $ffe_included = ( isset($_POST['ffe_included']) ) ? filter_var($_POST['ffe_included'], FILTER_SANITIZE_NUMBER_INT) : '';
        $real_estate_value = ( isset($_POST['real_estate_value']) ) ? filter_var($_POST['real_estate_value'], FILTER_SANITIZE_STRING) : '';
        $real_estate_included = ( isset($_POST['real_estate_included']) ) ? filter_var($_POST['real_estate_included'], FILTER_SANITIZE_NUMBER_INT) : '';
        $real_estate_for_sale = ( isset($_POST['real_estate_for_sale']) ) ? filter_var($_POST['real_estate_for_sale'], FILTER_SANITIZE_NUMBER_INT) : '';
        $real_estate_description = ( isset($_POST['real_estate_description']) ) ? filter_var($_POST['real_estate_description'], FILTER_SANITIZE_STRING) : '';
        $seller_financing_available = ( isset($_POST['seller_financing_available']) ) ? filter_var($_POST['seller_financing_available'], FILTER_SANITIZE_NUMBER_INT) : '';
        $seller_financing_description = ( isset($_POST['seller_financing_description']) ) ? filter_var($_POST['seller_financing_description'], FILTER_SANITIZE_STRING) : '';

        $franchise = ( isset($_POST['franchise']) ) ? filter_var($_POST['franchise'], FILTER_SANITIZE_NUMBER_INT) : '';
        $home_based = ( isset($_POST['home_based']) ) ? filter_var($_POST['home_based'], FILTER_SANITIZE_NUMBER_INT) : '';
        $relocatable = ( isset($_POST['relocatable']) ) ? filter_var($_POST['relocatable'], FILTER_SANITIZE_NUMBER_INT) : '';
        $lender_prequalified = ( isset($_POST['lender_prequalified']) ) ? filter_var($_POST['lender_prequalified'], FILTER_SANITIZE_NUMBER_INT) : '';

      //    test
      //    echo '(2) ' . $_POST['listing_status'] . '<br>';
      //    echo '(2) ' . $_POST['country'] . '<br>';
      //    echo '(2) ' . $_POST['state'] . '<br><br>';
      //
      //    echo '$listing_status => ' . $listing_status . '<br>';
      //    echo '$country => ' . $country . '<br>';
      //    echo '$state => ' . $state . '<br>';

        // Update data for listing in `listing_financial` table
        try {
            $sql = "UPDATE listing_financial SET
                    asking_price                  = :asking_price,
                    gross_income                  = :gross_income,
                    cash_flow                     = :cash_flow,
                    ebitda                        = :ebitda,
                    inventory_value               = :inventory_value,
                    inventory_included            = :inventory_included,
                    ffe_value                     = :ffe_value,
                    ffe_included                  = :ffe_included,
                    real_estate_value             = :real_estate_value,
                    real_estate_included          = :real_estate_included,
                    real_estate_for_sale          = :real_estate_for_sale,
                    real_estate_description       = :real_estate_description,
                    seller_financing_available    = :seller_financing_available,
                    seller_financing_description  = :seller_financing_description,
                    franchise                     = :franchise,
                    home_based                    = :home_based,
                    relocatable                   = :relocatable,
                    lender_prequalified           = :lender_prequalified
                    WHERE listing_id              = :listing_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                  ':asking_price'                 => $asking_price,
                  ':gross_income'                 => $gross_income,
                  ':cash_flow'                    => $cash_flow,
                  ':ebitda'                       => $ebitda,
                  ':inventory_value'              => $inventory_value,
                  ':inventory_included'           => $inventory_included,
                  ':ffe_value'                    => $ffe_value,
                  ':ffe_included'                 => $ffe_included,
                  ':real_estate_value'            => $real_estate_value,
                  ':real_estate_included'         => $real_estate_included,
                  ':real_estate_for_sale'         => $real_estate_for_sale,
                  ':real_estate_description'      => $real_estate_description,
                  ':seller_financing_available'   => $seller_financing_available,
                  ':seller_financing_description' => $seller_financing_description,
                  ':franchise'                    => $franchise,
                  ':home_based'                   => $home_based,
                  ':relocatable'                  => $relocatable,
                  ':lender_prequalified'          => $lender_prequalified,
                  ':listing_id'                   => $listing_id,
            ];
            $stmt->execute($parameters);
        }
        catch (PDOException $e) {
            echo "Error inserting listing data into database: " . $e->getMessage();
            exit();
        }
        return true;
    }




    public static function updateListingImages($id, $broker_id)
    {
        // Assign target directory based on server
        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_business_photos/';
        }
        else
        {
          // path for local machine
          $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';
        }


        /* - - - - - - - - - - - - - -  img01  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img01']['tmp_name']) && $_FILES['img01']['tmp_name'] != '')
        {
            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img01']['name'];
            $file_tmp = $_FILES['img01']['tmp_name'];
            $file_type = $_FILES['img01']['type'];
            $file_size = $_FILES['img01']['size'];
            $err_msg = $_FILES['img01']['error'];

            // get image width
            $size = getimagesize($_FILES['img01']['tmp_name']);
            // store in variable
            $img01_width = $size[0];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;

            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists - must be able to over-write
            // if (file_exists($target_dir . $file_name))
            // {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists.
            //     <br> Please select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152){
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name)){
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1){
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 )
            {

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - -   Image Re-sizing & over-writing   - - - - - - -  */
                // resize only if image > 750px wide
                if($img01_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }


            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img01 = :img01
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters  = [
                    ':listing_id' => $id,
                    ':img01'      => $file_name
                ];
                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }


        /* - - - - - - - - - - - - - -  img02  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img02']['tmp_name']) && $_FILES['img02']['tmp_name'] != '')
        {

            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img02']['name'];
            $file_tmp  = $_FILES['img02']['tmp_name'];
            $file_type = $_FILES['img02']['type'];
            $file_size = $_FILES['img02']['size'];
            $err_msg   = $_FILES['img02']['error'];

            // get image width
            $size = getimagesize($_FILES['img02']['tmp_name']);
            // store in variable
            $img02_width = $size[0];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable
            // (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;


            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists
            // if (file_exists($target_dir . $file_name))
            // {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists. <br> Please
            //     select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1)
            {
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 ){

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - -   Image Re-sizing & over-writing   - - - - - - -  */
                // resize only if image > 750px wide
                if($img02_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }



            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img02 = :img02
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id'=> $id,
                    ':img02'     => $file_name
                ];

                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }


        /* - - - - - - - - - - - - - -  img03  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img03']['tmp_name']) && $_FILES['img03']['tmp_name'] != '')
        {
            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img03']['name'];
            $file_tmp  = $_FILES['img03']['tmp_name'];
            $file_type = $_FILES['img03']['type'];
            $file_size = $_FILES['img03']['size'];
            $err_msg   = $_FILES['img03']['error'];

            // get image width
            $size = getimagesize($_FILES['img03']['tmp_name']);
            // store in variable
            $img03_width = $size[0];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable
            // (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;


            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists
            // if (file_exists($target_dir . $file_name)) {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists. <br> Please
            //     select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152){
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name)){
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1){
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 ){

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true) {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - - - -  Image Re-sizing & over-writing   - -  - - -  */
                // resize only if image > 750px wide
                if($img03_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }


            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img03 = :img03
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id'=> $id,
                    ':img03'     => $file_name
                ];

                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }


        /* - - - - - - - - - - - - - -  img04  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img04']['tmp_name']) && $_FILES['img04']['tmp_name'] != '')
        {

            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img04']['name'];
            $file_tmp = $_FILES['img04']['tmp_name'];
            $file_type = $_FILES['img04']['type'];
            $file_size = $_FILES['img04']['size'];
            $err_msg = $_FILES['img04']['error'];

            // get image width
            $size = getimagesize($_FILES['img04']['tmp_name']);
            // store in variable
            $img04_width = $size[0];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable
            // (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;


            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists
            // if (file_exists($target_dir . $file_name)) {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists. <br> Please
            //     select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152){
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name)){
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1){
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 ){

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true) {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - - -   Image Re-sizing & over-writing   - - - - - -  */
                // resize only if image > 750px wide
                if($img04_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }


            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img04 = :img04
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id'=> $id,
                    ':img04'     => $file_name
                ];

                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }


        /* - - - - - - - - - - - - - -  img05  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img05']['tmp_name']) && $_FILES['img05']['tmp_name'] != '')
        {
            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img05']['name'];
            $file_tmp  = $_FILES['img05']['tmp_name'];
            $file_type = $_FILES['img05']['type'];
            $file_size = $_FILES['img05']['size'];
            $err_msg   = $_FILES['img05']['error'];

            // get image width
            $size = getimagesize($_FILES['img05']['tmp_name']);
            // store in variable
            $img05_width = $size[0];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable
            // (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;


            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists
            // if (file_exists($target_dir . $file_name)) {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists. <br> Please
            //     select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152){
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name)){
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1){
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 ){

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true) {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - - - -  Image Re-sizing & over-writing   - - - - -  */
                // resize only if image > 750px wide
                if($img05_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }


            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img05 = :img05
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id'=> $id,
                    ':img05'     => $file_name
                ];

                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }


        /* - - - - - - - - - - - - - -  img06  - - - - - - - - - - - - - - -  */

        if(isset($_FILES['img06']['tmp_name']) && $_FILES['img06']['tmp_name'] != '')
        {
            // Access $_FILES global array for uploaded files
            $file_name = $_FILES['img06']['name'];
            $file_tmp = $_FILES['img06']['tmp_name'];
            $file_type = $_FILES['img06']['type'];
            $file_size = $_FILES['img06']['size'];
            $err_msg = $_FILES['img06']['error'];

            // get image width
            $size = getimagesize($_FILES['img06']['tmp_name']);
            // store in variable
            $img06_width = $size[0];


            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable
            // (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Prefix broker id and listing id to image name
            $prefix = $broker_id . '-' . $id . '-';
            $file_name = $prefix . $file_name;


            // Assign value to checker variable
            $upload_ok = 1;


            /* - - - - -  Error handling  - - - - - */

            // Check if file already exists
            // if (file_exists($target_dir . $file_name)) {
            //     $upload_ok = 0;
            //     echo nl2br('Sorry, image file already exists. <br> Please
            //     select a different file or rename file and try again.');
            //     exit();
            // }

            // Check if file size < 2 MB
            if($file_size > 2097152){
                $upload_ok = 0;
                unlink($file_tmp);
                echo nl2br('File too large. <br> Must be less than 2 Megabytes to upload.');
                exit();
            }

            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name)){
                $upload_ok = 0;
                unlink($file_tmp);
                echo 'Image must be gif, jpg, jpeg, or png to upload.';
                exit();
            }

            // Check for any errors
            if($err_msg == 1){
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if( $upload_ok = 1 ){

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true) {
                    unlink($file_tmp);
                    echo $file_name . ' not uploaded. Please try again.';
                    exit();
                }

                /*  - - - - - -  Image Re-sizing & over-writing   - - - - -  */
                // resize only if image > 750px wide
                if($img06_width > 750)
                {
                    include_once 'Library/image-resizing-to-scale.php';
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                    else
                    {
                      // path for local machine
                      $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_business_photos/$file_name";
                      $wmax = 750;
                      $hmax = 750;
                      image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                    }
                }
            }


            // Insert image paths into listing_images table
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE listing_images SET
                        img06 = :img06
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id'=> $id,
                    ':img06'     => $file_name
                ];

                $stmt->execute($parameters);
            }
            catch (PDOException $e)
            {
                echo "Error inserting image paths into database: " . $e->getMessage();
                exit();
            }
        }
        return true;
    }




    public static function deleteListingImage($listing_id, $image)
    {
        if($image != '')
        {

            try
            {
                // establish db connection
                $db = static::getDB();

                // get listing images
                $sql = "SELECT img01,img02,img03,img04,img05,img06
                        FROM listing_images
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id' => $listing_id
                ];
                $stmt->execute($parameters);
                $images = $stmt->fetch(PDO::FETCH_ASSOC);

                // test
                // echo '<pre>';
                // print_r($images);
                // echo '</pre>';
                //exit();

                // find match, store key
                foreach($images as $key => $value)
                {
                    if($value == $image)
                    {
                       $field = $key;
                    }
                }

                //echo $field; exit();

                // set field value to null
                $sql = "UPDATE listing_images SET
                        $field = ''
                        WHERE listing_id = :listing_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':listing_id' => $listing_id
                ];
                // execute; if successful delete file from server
                if($stmt->execute($parameters))
                {
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                      $file_path = Config::UPLOAD_PATH . '/assets/images/uploaded_business_photos/'.$image;
                    }
                    else
                    {
                      // path for local machine
                      $file_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/'.$image;
                    }

                    if(unlink($file_path))
                    {
                        // return to Brokers controller
                        return true;
                    }
                    else
                    {
                        // return to Brokers controller
                        return false;
                    }
                }
                else
                {
                    // return to Brokers controller
                    return false;
                }
            }
            catch(PDOException $e)
            {
                echo $e->getMessage();
                exit();
            }
        }
        else
        {
            // return to Brokers controller
            return false;
        }
    }




    public static function getSellPageListingDetails($listing_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM listing
                    INNER JOIN listing_financial
                    ON listing.listing_id = listing_financial.listing_id
                    WHERE listing.listing_id = :listing_id
                    AND listing.display = 0
                    AND listing.broker_id = 1";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id' => $listing_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $listing = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to controller
            return $listing;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * changes display setting so business listings will not display
     *
     * @param  integer $broker_id   The broker's ID
     * @return boolean
     */
    public static function updateBusinessListingsDisplayToFalse($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE listing SET
                    display = 0
                    WHERE broker_id = :broker_id";
            $parameters = [
                ':broker_id'  => $broker_id
            ];
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($parameters);

            // return $result (boolean)
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
