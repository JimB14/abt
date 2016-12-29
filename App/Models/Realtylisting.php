<?php

namespace App\Models;

use PDO;
use \App\Config;


/**
 * Realtylisting model
 */
class Realtylisting extends \Core\Model
{

    /**
     * gets records for particular broker by ID
     *
     * @param  Int $broker_id       The broker's ID
     * @param  Int $id              The agent's ID
     * @param  Int or null $limit   Count of records to return
     *
     * @return Object           The listings
     */
    public static function getListings($broker_id, $id, $limit)
    {
        if($broker_id != null)
        {
          $broker_id = "AND realty_listings.broker_id = '$broker_id'";
        }
        if($id != null)
        {
          $id = "AND broker_agents.id = '$id'";
        }
        if($limit != null)
        {
          $limit = 'LIMIT  ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT realty_listings.*,
                    broker_agents.id as agent_id,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone, broker_agents.cell as agent_cell,
                    broker_agents.address1, broker_agents.address2,
                    broker_agents.city as agent_city, broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip,
                    broker_agents.profile_photo, brokers.broker_id,
                    brokers.company_name, brokers.broker_id
                    FROM realty_listings
                    LEFT JOIN broker_agents
                    ON broker_agents.id = realty_listings.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = realty_listings.broker_id
                    WHERE realty_listings.display = '1'
                    $broker_id
                    $id
                    ORDER BY realty_listings.created_at DESC
                    $limit";

            $stmt = $db->prepare($sql);
            $stmt->execute();

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



    /**
     * gets any number of records
     *
     * @param  Int/null $limit  Number of records to return
     *
     * @return Object     The listings
     */
    public static function getRealtyListings($limit)
    {
        if($limit != null)
        {
          $limit = 'LIMIT  ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT realty_listings.*,
                    broker_agents.id as agent_id,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.profile_photo, brokers.broker_id,
                    brokers.company_name
                    FROM realty_listings
                    LEFT JOIN broker_agents
                    ON broker_agents.id = realty_listings.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = realty_listings.broker_id
                    WHERE realty_listings.display = '1'
                    ORDER BY realty_listings.created_at DESC
                    $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute();

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


    /**
     * retrieves listings for specicified agent and broker
     *
     * @param  integer  $broker_id  The broker ID
     * @param  integer  $agent_id   The agent's ID
     * @return object               The listings
     */
    public static function getListingsByAgent($broker_id, $agent_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM realty_listings
                    WHERE broker_id = :broker_id
                    AND listing_agent_id = :listing_agent_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'        => $broker_id,
                ':listing_agent_id' => $agent_id
            ];
            $stmt->execute($parameters);

            // store listing details in object
            $realty_listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Admin/Brokers Controller
            return $realty_listings;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * retrieves listings with matched keywords
     *
     * @return array The matched listings
     */
    public static function findRealEstateByKeyword()
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
            $where_keywords = "WHERE (realty_listings.keywords LIKE '%$keyword_array[0]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[0]%'
                                   OR realty_listings.description LIKE '%$keyword_array[0]%'
                                   OR realty_listings.city LIKE '$keyword_array[0]%'
                                   OR realty_listings.state LIKE '$keyword_array[0]%'
                                   )";
        }
        if(isset($keyword_count) && $keyword_count == 2)
        {
            $where_keywords = "WHERE (realty_listings.keywords LIKE '%$keyword_array[0]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[0]%'
                                   OR realty_listings.description LIKE '%$keyword_array[0]%'
                                   OR realty_listings.city LIKE '$keyword_array[0]%'
                                   OR realty_listings.state LIKE '$keyword_array[0]%'
                                   OR realty_listings.keywords LIKE '%$keyword_array[1]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[1]%'
                                   OR realty_listings.description LIKE '%$keyword_array[1]%'
                                   OR realty_listings.city LIKE '$keyword_array[1]%'
                                   OR realty_listings.state LIKE '$keyword_array[1]%'
                                   )";
        }
        if(isset($keyword_count) && $keyword_count == 3)
        {
            $where_keywords = "WHERE (realty_listings.keywords LIKE '%$keyword_array[0]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[0]%'
                                   OR realty_listings.description LIKE '%$keyword_array[0]%'
                                   OR realty_listings.city LIKE '$keyword_array[0]%'
                                   OR realty_listings.state LIKE '$keyword_array[0]%'
                                   OR realty_listings.keywords LIKE '%$keyword_array[1]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[1]%'
                                   OR realty_listings.description LIKE '%$keyword_array[1]%'
                                   OR realty_listings.city LIKE '$keyword_array[1]%'
                                   OR realty_listings.state LIKE '$keyword_array[1]%'
                                   OR realty_listings.keywords LIKE '%$keyword_array[2]%'
                                   OR realty_listings.ad_title LIKE '%$keyword_array[2]%'
                                   OR realty_listings.description LIKE '%$keyword_array[2]%'
                                   OR realty_listings.city LIKE '$keyword_array[2]%'
                                   OR realty_listings.state LIKE '$keyword_array[2]%'
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

            $sql = "SELECT realty_listings.*,
                    broker_agents.id as agent_id,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    brokers.broker_id,
                    brokers.company_name
                    FROM realty_listings
                    LEFT JOIN broker_agents
                    ON broker_agents.id = realty_listings.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = realty_listings.broker_id
                    $where_keywords
                    AND realty_listings.display = '1'
                    ORDER BY realty_listings.asking_price DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            // Store results in array
            $listings = $stmt->fetchAll(PDO::FETCH_OBJ);

            $results = [
                'listings' => $listings,
                'keywords' => $keyword_array
            ];

            // return array to Realty Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * gets real estate records for specified agent
     *
     * @param  integer          $broker_id  [description]
     * @param  string           $last_name  [description]
     * @param  integer          $clients_id [description]
     * @param  integer or null  $limit      [description]
     * @return array            The listings & pagetitle
     */
    public static function getRealtyListingsBySearchCriteria($broker_id, $last_name, $clients_id, $limit)
    {
        // if checkbox = false (not checked) & empty form is submitted
        if($clients_id == null && $last_name === '')
        {
            echo '<script>';
            echo 'alert("Please enter an agent last name.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id=' .$broker_id.'"';
            echo '</script>';
            exit();
        }

        // if checkbox = true (checked = on) & empty form submitted
        if($last_name == null && $clients_id === '')
        {
            echo '<script>';
            echo 'alert("Please enter a real estate listing ID.")';
            echo '</script>';

            // redirect user to same page
            echo '<script>';
            echo 'window.location.href="/admin/brokers/show-real-estate-listings?id=' .$broker_id.'"';
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
          $pagetitle = "Real estate listings by last name: $last_name_for_view";
        }
        if($clients_id != null)
        {
          $clients_id_for_view = $clients_id;
          $clients_id = "AND realty_listings.clients_id LIKE '$clients_id_for_view'";
          $pagetitle = "Real estate listing by ID: $clients_id_for_view";
        }

        // execute query
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM realty_listings
                    LEFT JOIN broker_agents
                    ON broker_agents.id = realty_listings.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = realty_listings.broker_id
                    WHERE realty_listings.broker_id = :broker_id
                    AND realty_listings.display = '1'
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


    /**
     * gets all listings for a particular agent by ID
     *
     * @param  Int $listing_agent_id The agent's ID
     *
     * @return Object                The listing data
     */
    public static function getAgentListings($listing_agent_id, $limit)
    {
        if($limit != null)
        {
          $limit = 'LIMIT  ' . $limit;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM realty_listings
                    WHERE realty_listings.display = '1'
                    AND listing_agent_id = :listing_agent_id
                    ORDER BY realty_listings.created_at DESC
                    $limit";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_agent_id' => $listing_agent_id
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




    public static function postNewRealEstateListing($broker_id)
    {
        // echo "Connected to postNewRealEstateListing method in Realtylisting model!<br><br>";
        // echo '$broker_id from postNewRealEstateListing method in Listing model: ' . $broker_id . "<br><br>";
        // exit();

        // Retrieve post data, sanitize and store in local variables
        $listing_agent_id = ( isset($_POST['listing_agent_id']) ) ? filter_var($_POST['listing_agent_id'], FILTER_SANITIZE_STRING) : '';
        $type = ( isset($_POST['type']) ) ? filter_var($_POST['type'], FILTER_SANITIZE_STRING) : '';
        $subtype = ( isset($_POST['subtype']) ) ? filter_var($_POST['subtype'], FILTER_SANITIZE_STRING) : '';
        $clients_id = ( isset($_POST['clients_id']) ) ? filter_var($_POST['clients_id'], FILTER_SANITIZE_STRING) : '';
        $ad_title = ( isset($_POST['ad_title']) ) ? filter_var($_POST['ad_title'], FILTER_SANITIZE_STRING) : '';

        $asking_price = ( isset($_POST['asking_price']) ) ? filter_var($_POST['asking_price'], FILTER_SANITIZE_STRING) : '';
        $date_available = ( isset($_POST['date_available']) ) ? filter_var($_POST['date_available'], FILTER_SANITIZE_STRING) : '';
        $square_feet = ( isset($_POST['square_feet']) ) ? filter_var($_POST['square_feet'], FILTER_SANITIZE_NUMBER_INT) : '';
        $acres = ( isset($_POST['acres']) ) ? filter_var($_POST['acres'], FILTER_SANITIZE_STRING) : '';

        $address = ( isset($_POST['address']) ) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : '';
        $address2 = ( isset($_POST['address2']) ) ? filter_var($_POST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $county = ( isset($_POST['county']) ) ? filter_var($_POST['county'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';

        $description = ( isset($_POST['description']) ) ? $_POST['description'] : '';
        $keywords = ( isset($_POST['keywords']) ) ? strtolower(filter_var($_POST['keywords'], FILTER_SANITIZE_STRING)) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // convert date_available to MySQL YYYY-mm-dd format
        $date_available = date('Y-m-d', strtotime($date_available));


        /* - - - - - insert data into db section - - - - - */

        // Insert data for new real estate listing into realty_listings table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO realty_listings SET
                    broker_id         = :broker_id,
                    listing_agent_id  = :listing_agent_id,
                    type              = :type,
                    subtype           = :subtype,
                    clients_id        = :clients_id,
                    ad_title          = :ad_title,
                    asking_price      = :asking_price,
                    date_available    = :date_available,
                    square_feet       = :square_feet,
                    acres             = :acres,
                    address           = :address,
                    address2          = :address2,
                    city              = :city,
                    state             = :state,
                    county            = :county,
                    zip               = :zip,
                    description       = :description,
                    keywords          = :keywords";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'        => $broker_id,
                ':listing_agent_id' => $listing_agent_id,
                ':type'             => $type,
                ':subtype'          => $subtype,
                ':clients_id'       => $clients_id,
                ':ad_title'         => $ad_title,
                ':asking_price'     => $asking_price,
                ':date_available'   => $date_available,
                ':square_feet'      => $square_feet,
                ':acres'            => $acres,
                ':address'          => $address,
                ':address2'         => $address2,
                ':city'             => $city,
                ':state'            => $state,
                ':county'           => $county,
                ':zip'              => $zip,
                ':description'      => $description,
                ':keywords'         => $keywords
            ];
            if( $stmt->execute($parameters) )
            {
                // Get realty_listing.id from this query for image filename
                $id = $db->lastInsertId();

                /* - - - - - upload brochure file - - - - - */

                // Assign target directory based on server
                if($_SERVER['SERVER_NAME'] != 'localhost')
                {
                  // path for live server
                  // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                  $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_brochures/';
                }
                else
                {
                  // path for local machine
                  $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_brochures/';
                }


                /* - - - - - - - - - - -  brochure  - - - - - - - - - - -- -  */

                if(isset($_FILES['brochure']['tmp_name']) && $_FILES['brochure']['tmp_name'] != '')
                {
                    // Access $_FILES global array for uploaded files
                    $file_name = $_FILES['brochure']['name'];
                    $file_tmp = $_FILES['brochure']['tmp_name'];
                    $file_type = $_FILES['brochure']['type'];
                    $file_size = $_FILES['brochure']['size'];
                    $err_msg = $_FILES['brochure']['error'];

                    // get image width
                    $size = getimagesize($_FILES['brochure']['tmp_name']);
                    // store in variable
                    $image_width = $size[0];

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

                    // Check if file size < 5 MB
                    if($file_size > 5242880){
                        $upload_ok = 0;
                        unlink($file_tmp);
                        echo nl2br('File too large. <br> Must be less than 5 Megabytes to upload.');
                        exit();
                    }

                    // Check if file is gif, jpg, jpeg or png
                    if(!preg_match("/\.(pdf|gif|jpg|jpeg|png)$/i", $file_name)){
                        $upload_ok = 0;
                        unlink($file_tmp);
                        echo 'Image must be pdf, gif, jpg, jpeg, or png to upload.';
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
                        if($image_width > 750)
                        {
                            include_once 'Library/image-resizing-to-scale.php';
                            // Assign target directory based on server
                            if($_SERVER['SERVER_NAME'] != 'localhost')
                            {
                              // path for live server
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                brochure = :brochure
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'       => $id,
                            ':brochure' => $file_name
                        ];
                        $stmt->execute($parameters);
                    }
                    catch (PDOException $e)
                    {
                        echo "Error inserting brochure file path into database: " . $e->getMessage();
                        exit();
                    }
                }


                /* - - - process image or images - - - */

                // Assign target directory based on server
                if($_SERVER['SERVER_NAME'] != 'localhost')
                {
                  // path for live server
                  // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                  $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_photos/';
                }
                else
                {
                  // path for local machine
                  $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_photos/';
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img01 = :img01
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'    => $id,
                            ':img01' => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }



                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img02 = :img02
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img02'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img03 = :img03
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img03'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img04 = :img04
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img04'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img05 = :img05
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img05'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img06 = :img06
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img06'  => $file_name
                        ];

                        $stmt->execute($parameters);
                    }
                    catch (PDOException $e)
                    {
                        echo "Error inserting image paths into database: " . $e->getMessage();
                        exit();
                    }
                }

                $result = true;

                // return boolean to Admin/Brokers Controller
                return $result;
            }
        }
        catch (PDOException $e)
        {
            echo "Error inserting data into database: " . $e->getMessage();
            exit();
        }
    }



    /**
     * delete real estate listing from realty_listings
     *
     * @param  Int $id Listing ID
     *
     * @return boolean
     */
    public static function deleteRealEstateListing($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM realty_listings
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Admin/Brokers Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * get listing details by ID
     *
     * @param  Int $id The listing ID
     *
     * @return Object     The listing data
     */
    public static function getRealEstateListing($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM realty_listings
                    WHERE id = :id";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
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
     * Updates field value with null for specified real estate listing by its ID
     *
     * @param  Int $id          The real estate listing record's ID
     * @param  String $image    Image file name
     * @return boolean
     */
    public static function deleteListingImage($id, $image)
    {
        if($image != '')
        {

            try
            {
                // establish db connection
                $db = static::getDB();

                // get all real estate listing images
                $sql = "SELECT img01,img02,img03,img04,img05,img06
                        FROM realty_listings
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
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
                $sql = "UPDATE realty_listings SET
                        $field = ''
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id' => $id
                ];
                // execute; if successful delete file from server
                if($stmt->execute($parameters))
                {
                    // Assign target directory based on server
                    if($_SERVER['SERVER_NAME'] != 'localhost')
                    {
                      // path for live server
                      // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                      $file_path = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_photos/'.$image;
                    }
                    else
                    {
                      // path for local machine
                      $file_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_photos/'.$image;
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


    /**
     * updates record by its ID
     *
     * @param  Int $id        The listing ID
     * @param  Int $broker_id The broker ID
     *
     * @return boolean
     */
    public static function updateRealEstateListing($id, $broker_id)
    {
        // Retrieve post data, sanitize and store in local variables
        $listing_agent_id = ( isset($_POST['listing_agent_id']) ) ? filter_var($_POST['listing_agent_id'], FILTER_SANITIZE_STRING) : '';
        $type = ( isset($_POST['type']) ) ? filter_var($_POST['type'], FILTER_SANITIZE_STRING) : '';
        $display = ( isset($_POST['display']) ) ? filter_var($_POST['display'], FILTER_SANITIZE_STRING) : '';
        $subtype = ( isset($_POST['subtype']) ) ? filter_var($_POST['subtype'], FILTER_SANITIZE_STRING) : '';
        $clients_id = ( isset($_POST['clients_id']) ) ? filter_var($_POST['clients_id'], FILTER_SANITIZE_STRING) : '';
        $ad_title = ( isset($_POST['ad_title']) ) ? filter_var($_POST['ad_title'], FILTER_SANITIZE_STRING) : '';

        $asking_price = ( isset($_POST['asking_price']) ) ? filter_var($_POST['asking_price'], FILTER_SANITIZE_STRING) : '';
        $date_available = ( isset($_POST['date_available']) ) ? filter_var($_POST['date_available'], FILTER_SANITIZE_STRING) : '';
        $square_feet = ( isset($_POST['square_feet']) ) ? filter_var($_POST['square_feet'], FILTER_SANITIZE_NUMBER_INT) : '';
        $acres = ( isset($_POST['acres']) ) ? filter_var($_POST['acres'], FILTER_SANITIZE_STRING) : '';

        $address = ( isset($_POST['address']) ) ? filter_var($_POST['address'], FILTER_SANITIZE_STRING) : '';
        $address2 = ( isset($_POST['address2']) ) ? filter_var($_POST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $county = ( isset($_POST['county']) ) ? filter_var($_POST['county'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';

        $description = ( isset($_POST['description']) ) ? $_POST['description'] : '';
        $keywords = ( isset($_POST['keywords']) ) ? strtolower(filter_var($_POST['keywords'], FILTER_SANITIZE_STRING)) : '';

        // test
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        // exit();

        // convert date_available to MySQL YYYY-mm-dd format
        $date_available = date('Y-m-d', strtotime($date_available));


        /* - - - - - insert data into db section - - - - - */

        // Insert data for new real estate listing into realty_listings table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE realty_listings SET
                    listing_agent_id  = :listing_agent_id,
                    type              = :type,
                    display           = :display,
                    subtype           = :subtype,
                    clients_id        = :clients_id,
                    ad_title          = :ad_title,
                    asking_price      = :asking_price,
                    date_available    = :date_available,
                    square_feet       = :square_feet,
                    acres             = :acres,
                    address           = :address,
                    address2          = :address2,
                    city              = :city,
                    state             = :state,
                    county            = :county,
                    zip               = :zip,
                    description       = :description,
                    keywords          = :keywords
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'               => $id,
                ':listing_agent_id' => $listing_agent_id,
                ':type'             => $type,
                ':display'          => $display,
                ':subtype'          => $subtype,
                ':clients_id'       => $clients_id,
                ':ad_title'         => $ad_title,
                ':asking_price'     => $asking_price,
                ':date_available'   => $date_available,
                ':square_feet'      => $square_feet,
                ':acres'            => $acres,
                ':address'          => $address,
                ':address2'         => $address2,
                ':city'             => $city,
                ':state'            => $state,
                ':county'           => $county,
                ':zip'              => $zip,
                ':description'      => $description,
                ':keywords'         => $keywords
            ];
            if( $stmt->execute($parameters) )
            {

                /* - - - - - upload brochure file - - - - - */

                // Assign target directory based on server
                if($_SERVER['SERVER_NAME'] != 'localhost')
                {
                  // path for live server
                  // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                  $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_brochures/';
                }
                else
                {
                  // path for local machine
                  $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_brochures/';
                }


                /* - - - - - - - - - - -  brochure  - - - - - - - - - - -- -  */

                if(isset($_FILES['brochure']['tmp_name']) && $_FILES['brochure']['tmp_name'] != '')
                {
                    // Access $_FILES global array for uploaded files
                    $file_name = $_FILES['brochure']['name'];
                    $file_tmp = $_FILES['brochure']['tmp_name'];
                    $file_type = $_FILES['brochure']['type'];
                    $file_size = $_FILES['brochure']['size'];
                    $err_msg = $_FILES['brochure']['error'];

                    // get image width
                    $size = getimagesize($_FILES['brochure']['tmp_name']);
                    // store in variable
                    $image_width = $size[0];

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

                    // Check if file size < 5 MB
                    if($file_size > 5242880){
                        $upload_ok = 0;
                        unlink($file_tmp);
                        echo nl2br('File too large. <br> Must be less than 5 Megabytes to upload.');
                        exit();
                    }

                    // Check if file is gif, jpg, jpeg or png
                    if(!preg_match("/\.(pdf|gif|jpg|jpeg|png)$/i", $file_name)){
                        $upload_ok = 0;
                        unlink($file_tmp);
                        echo 'Image must be pdf, gif, jpg, jpeg, or png to upload.';
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
                        if($image_width > 750)
                        {
                            include_once 'Library/image-resizing-to-scale.php';
                            // Assign target directory based on server
                            if($_SERVER['SERVER_NAME'] != 'localhost')
                            {
                              // path for live server
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_brochures/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                brochure = :brochure
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'       => $id,
                            ':brochure' => $file_name
                        ];
                        $stmt->execute($parameters);
                    }
                    catch (PDOException $e)
                    {
                        echo "Error inserting brochure file path into database: " . $e->getMessage();
                        exit();
                    }
                }


                /* - - - process image or images - - - */

                // Assign target directory based on server
                if($_SERVER['SERVER_NAME'] != 'localhost')
                {
                  // path for live server
                  // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
                  $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_real_estate_photos/';
                }
                else
                {
                  // path for local machine
                  $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_real_estate_photos/';
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img01 = :img01
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'    => $id,
                            ':img01' => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }



                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img02 = :img02
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img02'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img03 = :img03
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img03'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img04 = :img04
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img04'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img05 = :img05
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img05'  => $file_name
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
                              $target_file  = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = Config::UPLOAD_PATH . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                            else
                            {
                              // path for local machine
                              $target_file  = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $resized_file = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploaded_real_estate_photos/$file_name";
                              $wmax = 750;
                              $hmax = 750;
                              image_resize($target_file, $resized_file, $wmax, $hmax, $file_extension);
                            }
                        }
                    }


                    // Insert image paths into realty_listings table
                    try
                    {
                        // establish db connection
                        $db = static::getDB();

                        $sql = "UPDATE realty_listings SET
                                img06 = :img06
                                WHERE id = :id";
                        $stmt = $db->prepare($sql);
                        $parameters = [
                            ':id'     => $id,
                            ':img06'  => $file_name
                        ];

                        $stmt->execute($parameters);
                    }
                    catch (PDOException $e)
                    {
                        echo "Error inserting image paths into database: " . $e->getMessage();
                        exit();
                    }
                }

                $result = true;

                // return boolean to Admin/Brokers Controller
                return $result;
            }
        }
        catch (PDOException $e)
        {
            echo "Error inserting data into database: " . $e->getMessage();
            exit();
        }
    }

    /* - - - - - - - - Search functionality - - - - - - - - - - - - - - -  */


    /**
     * retrieves real estate listing records by search criteria
     *
     * @param  Int $offset Position of first record to return
     * @param  Int $count  Max number of records to return
     *
     * @return Object       The records
     */
    public static function findRealEstateBySearchCriteria($offset, $count)
    {
        // retrieve data from form
        $type    = ( isset($_REQUEST['type']) ) ? filter_var($_REQUEST['type'],FILTER_SANITIZE_STRING) : '';
        $subtype = ( isset($_REQUEST['subtype']) ) ? filter_var($_REQUEST['subtype'],FILTER_SANITIZE_STRING) : '';
        $state   = ( isset($_REQUEST['state']) ) ? filter_var($_REQUEST['state'],FILTER_SANITIZE_STRING) : '';
        $county  = ( isset($_REQUEST['county']) ) ? filter_var($_REQUEST['county'],FILTER_SANITIZE_STRING) : '';
        $zip     = ( isset($_REQUEST['zip']) ) ? filter_var($_REQUEST['zip'],FILTER_SANITIZE_STRING) : '';


        // if type 'sale' & subtype selected
        if($type === 'sale' && $subtype != 'All categories')
        {
            $where_category = "WHERE realty_listings.type = '$type'
                               AND realty_listings.subtype = '$subtype'";
        }

        // if type 'sale' only selected
        if($type === 'sale' && $subtype === 'All categories')
        {
            $where_category = "WHERE realty_listings.type = '$type'";
        }


        // if type 'lease' & subtype selected
        if($type === 'lease' && $subtype != 'All categories')
        {
            $where_category = "WHERE realty_listings.type = '$type'
                               AND realty_listings.subtype = '$subtype'";
        }

        // if type 'lease' only selected
        if($type === 'lease' && $subtype === 'All categories')
        {
            $where_category = "WHERE realty_listings.type = '$type'";
        }

        // echo $type . '<br>';
        // echo $subtype . '<br>';
        // echo $where_category . '<br><br>';
        // exit();


        // test
        // echo "Type: " . $type . '<br>';
        // echo "Subtype: " . $subtype . '<br>';
        // echo "State: " . $state . '<br>';
        // echo "County: " . $county . '<br>';
        // echo "Zip: " . $zip . '<br><br>';
        // exit();

        /*  If user makes no particular selection that filter must be removed
         *  from query; but if used, included in query
         */

         // If no zip selected
         if ($zip === '')
         {
             $where_zip = '';
         }
         else
         {
             // If zip is selected
             $where_zip = "AND realty_listings.zip = '$zip'";
         }

        // If no state selected
        if ($state === 'all' || $state == '')
        {
            $where_state = '';
        }
        else
        {
            // If state is selected
            $where_state = "AND realty_listings.state = '$state'";
        }

        // If no county is selected
        if ($county === 'all' || $county === 'All counties' || $county == '')
        {
            $where_county = '';
        }
        else
        {
            // If county is selected
            $where_county = "AND realty_listings.county = '$county'";
        }

        // code if country field added
        // If state and country is selected
        // if ($where_category === '' && $where_subcategory === '' && $where_state != '' && $where_county != '')
        // {
        //     $where_category = '';
        //     $where_state  = 'WHERE listing.state = :state';
        //     $where_county = 'AND listing.county = :county';
        // }

        // test
        // echo '$where_category => ' . $where_category . '<br>';
        // echo '$where_state => ' . $where_state . '<br>';
        // echo '$where_county => ' . $where_county . '<br>';
        // echo '$where_zip => ' . $where_zip . '<br><br><br>';
        // exit();

        // test
        // echo '$type => ' . $type . '<br>';
        // echo '$subtype => ' . $subtype . '<br>';
        // echo '$state => ' . $state . '<br>';
        // echo '$county => ' . $county . '<br>';
        // echo '$zip => ' . $zip . '<br><br>';
        // exit();

        // Retrieve listing data from tables
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT realty_listings.*,
                    broker_agents.id as agent_id,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as  agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone, broker_agents.cell as agent_cell,
                    broker_agents.address1, broker_agents.address2,
                    broker_agents.city as agent_city, broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip,
                    broker_agents.profile_photo, brokers.broker_id,
                    brokers.company_name, brokers.broker_id
                    FROM realty_listings
                    LEFT JOIN broker_agents
                    ON broker_agents.id = realty_listings.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = realty_listings.broker_id
                    $where_category
                    $where_state
                    $where_county
                    $where_zip
                    AND realty_listings.display = '1'
                    ORDER BY created_at DESC
                    LIMIT $offset, $count";

            $stmt = $db->prepare($sql);
            $stmt->execute();

            // Store results in array
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // create array to pass values back to Buy Controller
            $results = [
                'listings'  => $listings,
                'type'      => $type,
                'subtype'   => $subtype,
                'state'     => $state,
                'county'    => $county,
                'zip'       => $zip
            ];

            // return $results to Buy Controller
            return $results;

        }
        catch (PDOException $e) {
            echo "Error fetching real estate listings from database" . $e->getMessage();
            exit();
        }
    }



    /**
     * changes display setting so real estate listings will not display
     *
     * @param  integer $broker_id   The broker's ID
     * @return boolean
     */
    public static function updateRealtyListingsDisplayToFalse($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE realty_listings SET
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
