<?php

namespace App\Models;

use PDO;
use \App\Config;

/**
 * BrokerAgent model
 */
class BrokerAgent extends \Core\Model
{

    public static function getAgent($agent_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM broker_agents WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $agent_id
            ];
            $stmt->execute($parameters);
            $agent = $stmt->fetch(PDO::FETCH_OBJ);

            return $agent;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /**
     * retrieve agent of particular broker by last_name field search
     *
     * @param  int $broker_id broker ID
     * @param  string $last_name name of agent being searched
     *
     * @return object            agent data or null
     */
    public static function getAgents($broker_id, $last_name)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT broker_agents.id as agent_id, broker_agents.status,
                    broker_agents.type,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone,
                    broker_agents.cell as agent_cell,
                    broker_agents.address1 as agent_address1,
                    broker_agents.address2 as agent_address2,
                    broker_agents.city as agent_city,
                    broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip, broker_agents.about_me,
                    broker_agents.profile_photo, broker_agents.affiliations,
                    broker_agents.state_serv01,broker_agents.state_serv02,
                    broker_agents.state_serv03, broker_agents.state_serv04,
                    broker_agents.state_serv05,
                    broker_agents.counties_serv01,
                    broker_agents.counties_serv02, broker_agents.counties_serv03,
                    broker_agents.counties_serv04, broker_agents.counties_serv05
                    FROM broker_agents
                    WHERE broker_id = :broker_id
                    AND last_name LIKE '$last_name%'
                    ORDER BY last_name";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id,
            ];
            $stmt->execute($parameters);
            $agents = $stmt->fetchAll(PDO::FETCH_OBJ);

            // create page title
            $pagetitle = "Manage agents - $last_name";

            // store results in associative array
            $results = [
                'agents'    => $agents,
                'pagetitle' => $pagetitle
            ];

            // return assoc array to Brokers Controller
            return $results;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }



    /*  Modify to be broker specific  */
    public static function getStatesServed($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT state_serv01, state_serv02, state_serv03, state_serv04,
                    state_serv05
                    FROM broker_agents WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            // associative array; obj will not implode
            $results = $stmt->fetch(PDO::FETCH_ASSOC);

            $states_served = implode(' ', $results);

            return $states_served;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getAllBrokerAgents($limit, $broker_id, $orderby)
    {
        if($broker_id != null)
        {
            $broker_id = "WHERE brokers.broker_id = $broker_id";
        }
        if($limit != null)
        {
            $limit = 'LIMIT ' . $limit;
        }
        if($orderby != null)
        {
            $orderby = 'ORDER BY ' . $orderby;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT brokers.broker_id, brokers.type,
                    brokers.first_name as broker_first_name,
                    brokers.last_name as broker_last_name,
                    brokers.broker_email, brokers.address1,
                    brokers.address2, brokers.city, brokers.state, brokers.zip,
                    brokers.telephone, brokers.fax, brokers.company_name,
                    brokers.company_logo, brokers.company_bio, brokers.services,
                    brokers.website,
                    broker_agents.id as agent_id, broker_agents.status,
                    broker_agents.type,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone,
                    broker_agents.cell as agent_cell,
                    broker_agents.address1 as agent_address1,
                    broker_agents.address2 as agent_address2,
                    broker_agents.city as agent_city,
                    broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip, broker_agents.about_me,
                    broker_agents.profile_photo, broker_agents.affiliations,
                    broker_agents.state_serv01,broker_agents.state_serv02,
                    broker_agents.state_serv03, broker_agents.state_serv04,
                    broker_agents.state_serv05,
                    broker_agents.counties_serv01,
                    broker_agents.counties_serv02, broker_agents.counties_serv03,
                    broker_agents.counties_serv04, broker_agents.counties_serv05,
                    broker_agents.regDate, broker_agents.updated
                    FROM brokers
                    INNER JOIN broker_agents
                    ON brokers.broker_id = broker_agents.broker_id
                    $broker_id
                    $orderby
                    $limit";
              $stmt = $db->prepare($sql);
              $parameters = [
                  ':broker_id' => $broker_id
              ];
              $stmt->execute($parameters);

              $agents = $stmt->fetchAll(PDO::FETCH_OBJ);

              return $agents;
        }
        catch (PDOException $e)
        {
           echo $e->getMessage();
           exit();
        }
    }



    public static function getAllBrokerAgentsByType($type, $limit, $broker_id, $orderby)
    {
        if($broker_id != null)
        {
            $broker_id = "WHERE brokers.broker_id = $broker_id";
        }
        if($limit != null)
        {
            $limit = 'LIMIT ' . $limit;
        }
        if($orderby != null)
        {
            $orderby = 'ORDER BY ' . $orderby;
        }
        if($type != null)
        {
            $string = implode(', ', $type);
            $type = "AND broker_agents.type IN ($string)";
        }


        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT brokers.broker_id, brokers.type,
                    brokers.first_name as broker_first_name,
                    brokers.last_name as broker_last_name,
                    brokers.broker_email, brokers.address1,
                    brokers.address2, brokers.city, brokers.state, brokers.zip,
                    brokers.telephone, brokers.fax, brokers.company_name,
                    brokers.company_logo, brokers.company_bio, brokers.services,
                    brokers.website,
                    broker_agents.id as agent_id, broker_agents.status,
                    broker_agents.type,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone,
                    broker_agents.cell as agent_cell,
                    broker_agents.address1 as agent_address1,
                    broker_agents.address2 as agent_address2,
                    broker_agents.city as agent_city,
                    broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip, broker_agents.about_me,
                    broker_agents.profile_photo, broker_agents.affiliations,
                    broker_agents.state_serv01,broker_agents.state_serv02,
                    broker_agents.state_serv03, broker_agents.state_serv04,
                    broker_agents.state_serv05,
                    broker_agents.counties_serv01,
                    broker_agents.counties_serv02, broker_agents.counties_serv03,
                    broker_agents.counties_serv04, broker_agents.counties_serv05,
                    broker_agents.regDate, broker_agents.updated
                    FROM brokers
                    INNER JOIN broker_agents
                    ON brokers.broker_id = broker_agents.broker_id
                    $broker_id
                    $type
                    $orderby
                    $limit";
              $stmt = $db->prepare($sql);
              $parameters = [
                  ':broker_id' => $broker_id
              ];
              $stmt->execute($parameters);

              $agents = $stmt->fetchAll(PDO::FETCH_OBJ);

              return $agents;
        }
        catch (PDOException $e)
        {
           echo $e->getMessage();
           exit();
        }
    }




    public static function getCountOfAllBrokerAgents()
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $stmt = $db->query("SELECT * FROM broker_agents");
            $agent_count = $stmt->rowCount();

            return $agent_count;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getCountOfAgents($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM broker_agents
                    WHERE broker_id = :broker_id";
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt = $db->prepare($sql);
            $stmt->execute($parameters);
            $agent_count = $stmt->rowCount();

            return $agent_count;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getNamesOfAllBrokerAgents($broker_id, $orderby)
    {
        if($orderby != null)
        {
          $orderby = 'ORDER BY ' . $orderby;
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT broker_agents.id as agent_id,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.broker_id as broker_id,
                    broker_agents.type
                    FROM broker_agents
                    INNER JOIN brokers
                    ON brokers.broker_id = broker_agents.broker_id
                    WHERE broker_agents.broker_id = :broker_id
                    $orderby";
              $stmt = $db->prepare($sql);
              $parameters = [
                  ':broker_id' => $broker_id
              ];
              $stmt->execute($parameters);

              $agents = $stmt->fetchAll(PDO::FETCH_OBJ);

              return $agents;
        }
         catch (PDOException $e)
         {
           echo $e->getMessage();
           exit();
        }
    }




    public static function checkIfAgentAlreadyExists($first_name, $last_name, $broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // check db for matching names
            $sql = "SELECT first_name, last_name
                    FROM   broker_agents
                    WHERE  broker_id = :broker_id
                    AND    first_name = :first_name
                    AND    last_name = :last_name";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id,
                ':first_name' => $first_name,
                ':last_name' => $last_name
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function postNewAgent($broker_id)
    {
        // Displayed fields
        $first_name = ( isset($_POST['first_name']) )? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = ( isset($_POST['last_name']) )? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING): '';

        $status = ( isset($_POST['status']) )? filter_var($_POST['status'], FILTER_SANITIZE_STRING): '';
        $type = ( isset($_POST['type']) )? filter_var($_POST['type'], FILTER_SANITIZE_NUMBER_INT): '';

        $about_me = ( isset($_POST['about_me']) )? $_POST['about_me'] : '';

        $cell = ( isset($_POST['cell']) )? filter_var($_POST['cell'], FILTER_SANITIZE_STRING): '';
        $agent_email = ( isset($_POST['agent_email']) )? filter_var($_POST['agent_email'], FILTER_SANITIZE_STRING): '';
        $agent_telephone = ( isset($_POST['agent_telephone']) )? filter_var($_POST['agent_telephone'], FILTER_SANITIZE_STRING): '';

        $address1 = ( isset($_POST['agent_address1']) )? filter_var($_POST['agent_address1'], FILTER_SANITIZE_STRING): '';
        $address2 = ( isset($_POST['agent_address2']) )? filter_var($_POST['agent_address2'], FILTER_SANITIZE_STRING): '';
        $city = ( isset($_POST['agent_city']) )? filter_var($_POST['agent_city'], FILTER_SANITIZE_STRING): '';
        $state = ( isset($_POST['agent_state']) )? filter_var($_POST['agent_state'], FILTER_SANITIZE_STRING): '';
        $zip = ( isset($_POST['agent_zip']) )? filter_var($_POST['agent_zip'], FILTER_SANITIZE_STRING): '';

        $affiliations = ( isset($_POST['affiliations']) )? $_POST['affiliations'] : '';

        $state_serv01 = ( isset($_POST['state01']) )? filter_var($_POST['state01'], FILTER_SANITIZE_STRING): '';
        $state_serv02 = ( isset($_POST['state02']) )? filter_var($_POST['state02'], FILTER_SANITIZE_STRING): '';
        $state_serv03 = ( isset($_POST['state03']) )? filter_var($_POST['state03'], FILTER_SANITIZE_STRING): '';
        $state_serv04 = ( isset($_POST['state04']) )? filter_var($_POST['state04'], FILTER_SANITIZE_STRING): '';
        $state_serv05 = ( isset($_POST['state05']) )? filter_var($_POST['state05'], FILTER_SANITIZE_STRING): '';

        // if(isset($_POST['counties_served01'])) {echo 'Set';}else{echo 'Not set';} exit;

        if(isset($_POST['counties_serv01']))
        {
            $counties_serv01 = $_POST['counties_serv01'];
        }
        if(!empty($counties_serv01))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv01 = implode(', ', $counties_serv01);
        }
        else
        {
            $counties_serv01 = '';
        }


        if(isset($_POST['counties_serv02']))
        {
            $counties_serv02 = $_POST['counties_serv02'];
        }
        if(!empty($counties_serv02))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv02 = implode(', ', $counties_serv02);
        }
        else
        {
            $counties_serv02 = '';
        }


        if(isset($_POST['counties_serv03']))
        {
            $counties_serv03 = $_POST['counties_serv03'];
        }
        if(!empty($counties_serv03))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv03 = implode(', ', $counties_serv03);
        }
        else
        {
            $counties_serv03 = '';
        }


        if(isset($_POST['counties_serv04']))
        {
            $counties_serv04 = $_POST['counties_serv04'];
        }
        if(!empty($counties_serv04))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv04 = implode(', ', $counties_serv04);
        }
        else
        {
            $counties_serv04 = '';
        }


        if(isset($_POST['counties_serv05']))
        {
            $counties_serv05 = $_POST['counties_serv05'];
        }
        if(!empty($counties_serv05))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv05 = implode(', ', $counties_serv05);
        }
        else
        {
            $counties_serv05 = '';
        }



        // Assign target directory to variable
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_profile_photos/';

        if($_SERVER['SERVER_NAME'] != 'localhost')
        {
          // path for live server
          // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
          $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_profile_photos/';
        }
        else
        {
          // path for local machine
          $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_business_photos/';
        }

        // test
        // echo '$target_dir: ' . $target_dir . '<br>';
        // exit();

        // Access $_FILES global array for uploaded file
        $file_name = $_FILES['profile_photo']['name'];
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_type = $_FILES['profile_photo']['type'];
        $file_size = $_FILES['profile_photo']['size'];
        $file_err_msg = $_FILES['profile_photo']['error'];

        // Separate file name into an array by the dot
        $kaboom = explode(".", $file_name);

        // Assign last element of array to file_extension variable (in case file has more than one dot)
        $file_extension = end($kaboom);

        // Assign value to prefix for broker & listing specific image identification
        $prefix = $broker_id.'-'.time(). '-';

        /* - - - - -  Error handling  - - - - - - */

        $upload_ok = 1;

        // Check if file already exists
        if ( file_exists($target_dir . $file_name) )
        {
            // if (file_exists($target_file))
            $upload_ok = 0;
            echo "Sorry, profile photo file already exists. Please select a
                  different file or rename file and try again.";
            exit();
        }

        // Check if file size < 2 MB
        if($file_size > 2097152)
        {
            $upload_ok = 0;
            unlink($file_tmp);
            echo 'File must be less than 2 Megabytes to upload.';
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
            $file_name = $prefix . $file_name;

             // Upload file to server into designated folder
            $move_result = move_uploaded_file($file_tmp, $target_dir . $file_name);

            // Check for boolean result of move_uploaded_file()
            if ($move_result != true)
            {
                unlink($file_tmp_loc);
                echo 'File not uploaded. Please try again.';
                exit();
            }
        }
        else
        {
            echo 'File not uploaded. Please try again.';
            exit();
        }

        // Add new business broker agent profile data to broker_agents table (with profile photo)
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO broker_agents SET
                    broker_id       = :broker_id,
                    status          = :status,
                    type            = :type,
                    first_name      = :first_name,
                    last_name       = :last_name,
                    about_me        = :about_me,
                    profile_photo   = :profile_photo,
                    cell            = :cell,
                    agent_email     = :agent_email,
                    agent_telephone = :agent_telephone,
                    address1        = :address1,
                    address2        = :address2,
                    city            = :city,
                    state           = :state,
                    zip             = :zip,
                    affiliations    = :affiliations,
                    state_serv01    = :state_serv01,
                    state_serv02    = :state_serv02,
                    state_serv03    = :state_serv03,
                    state_serv04    = :state_serv04,
                    state_serv05    = :state_serv05,
                    counties_serv01 = :counties_serv01,
                    counties_serv02 = :counties_serv02,
                    counties_serv03 = :counties_serv03,
                    counties_serv04 = :counties_serv04,
                    counties_serv05 = :counties_serv05";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'       => $broker_id,
                ':status'          => $status,
                ':type'            => $type,
                ':first_name'      => $first_name,
                ':last_name'       => $last_name,
                ':about_me'        => $about_me,
                ':profile_photo'   => $file_name,
                ':cell'            => $cell,
                ':agent_email'     => $agent_email,
                ':agent_telephone' => $agent_telephone,
                ':address1'        => $address1,
                ':address2'        => $address2,
                ':city'            => $city,
                ':state'           => $state,
                ':zip'             => $zip,
                ':affiliations'    => $affiliations,
                ':state_serv01'    => $state_serv01,
                ':state_serv02'    => $state_serv02,
                ':state_serv03'    => $state_serv03,
                ':state_serv04'    => $state_serv04,
                ':state_serv05'    => $state_serv05,
                ':counties_serv01' => $counties_serv01,
                ':counties_serv02' => $counties_serv02,
                ':counties_serv03' => $counties_serv03,
                ':counties_serv04' => $counties_serv04,
                ':counties_serv05' => $counties_serv05
            ];
            $result = $stmt->execute($parameters);

            return $result;

            // Get id of last insert (broker_agents.id)
            $broker_agent_id = $db->lastInsertId();  // ID of new agent for this broker
        }
        catch (PDOException $e)
        {
            echo "Error inserting new agent data into database" . $e->getMessage();
            exit();
        }
    }




    public static function postNewAgentNoPhoto($broker_id)
    {
        // Displayed fields
        $first_name = ( isset($_POST['first_name']) )? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING): '';
        $last_name = ( isset($_POST['last_name']) )? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING): '';
        $status = ( isset($_POST['status']) )? filter_var($_POST['status'], FILTER_SANITIZE_STRING): '';
        $type = ( isset($_POST['type']) )? filter_var($_POST['type'], FILTER_SANITIZE_NUMBER_INT): '';
        $about_me = ( isset($_POST['about_me']) )? filter_var($_POST['about_me'], FILTER_SANITIZE_STRING): '';
        $cell = ( isset($_POST['cell']) )? filter_var($_POST['cell'], FILTER_SANITIZE_STRING): '';
        $agent_email = ( isset($_POST['agent_email']) )? filter_var($_POST['agent_email'], FILTER_SANITIZE_STRING): '';
        $agent_telephone = ( isset($_POST['agent_telephone']) )? filter_var($_POST['agent_telephone'], FILTER_SANITIZE_STRING): '';

        $address1 = ( isset($_POST['agent_address1']) )? filter_var($_POST['agent_address1'], FILTER_SANITIZE_STRING): '';
        $address2 = ( isset($_POST['agent_address2']) )? filter_var($_POST['agent_address2'], FILTER_SANITIZE_STRING): '';
        $city = ( isset($_POST['agent_city']) )? filter_var($_POST['agent_city'], FILTER_SANITIZE_STRING): '';
        $state = ( isset($_POST['agent_state']) )? filter_var($_POST['agent_state'], FILTER_SANITIZE_STRING): '';
        $zip = ( isset($_POST['agent_zip']) )? filter_var($_POST['agent_zip'], FILTER_SANITIZE_STRING): '';

        $affiliations = ( isset($_POST['affiliations']) )? filter_var($_POST['affiliations'], FILTER_SANITIZE_STRING): '';
        $state_serv01 = ( isset($_POST['state01']) )? filter_var($_POST['state01'], FILTER_SANITIZE_STRING): '';
        $state_serv02 = ( isset($_POST['state02']) )? filter_var($_POST['state02'], FILTER_SANITIZE_STRING): '';
        $state_serv03 = ( isset($_POST['state03']) )? filter_var($_POST['state03'], FILTER_SANITIZE_STRING): '';
        $state_serv04 = ( isset($_POST['state04']) )? filter_var($_POST['state04'], FILTER_SANITIZE_STRING): '';
        $state_serv05 = ( isset($_POST['state05']) )? filter_var($_POST['state05'], FILTER_SANITIZE_STRING): '';

        // if(isset($_POST['counties_served01'])) {echo 'Set';}else{echo 'Not set';} exit;

        if(isset($_POST['counties_serv01']))
        {
            $counties_serv01 = $_POST['counties_serv01'];
        }
        if(!empty($counties_serv01))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv01 = implode(', ', $counties_serv01);
        }
        else
        {
            $counties_serv01 = '';
        }


        if(isset($_POST['counties_serv02']))
        {
            $counties_serv02 = $_POST['counties_serv02'];
        }
        if(!empty($counties_serv02)){
            // Implode $counties_served array into comma separated string
            $counties_serv02 = implode(', ', $counties_serv02);
        }
        else
        {
            $counties_serv02 = '';
        }


        if(isset($_POST['counties_serv03']))
        {
            $counties_serv03 = $_POST['counties_serv03'];
        }
        if(!empty($counties_serv03))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv03 = implode(', ', $counties_serv03);
        }
        else
        {
            $counties_serv03 = '';
        }


        if(isset($_POST['counties_serv04']))
        {
            $counties_serv04 = $_POST['counties_serv04'];
        }
        if(!empty($counties_serv04))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv04 = implode(', ', $counties_serv04);
        }
        else
        {
            $counties_serv04 = '';
        }


        if(isset($_POST['counties_serv05']))
        {
            $counties_serv05 = $_POST['counties_serv05'];
        }
        if(!empty($counties_serv05))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv05 = implode(', ', $counties_serv05);
        }
        else
        {
            $counties_serv05 = '';
        }



        // Add new business broker agent profile data to broker_agents table (no profile photo)
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO broker_agents SET
                    broker_id       = :broker_id,
                    status          = :status,
                    type            = :type,
                    first_name      = :first_name,
                    last_name       = :last_name,
                    about_me        = :about_me,
                    cell            = :cell,
                    agent_email     = :agent_email,
                    agent_telephone = :agent_telephone,
                    address1        = :address1,
                    address2        = :address2,
                    city            = :city,
                    state           = :state,
                    zip             = :zip,
                    affiliations    = :affiliations,
                    state_serv01    = :state_serv01,
                    state_serv02    = :state_serv02,
                    state_serv03    = :state_serv03,
                    state_serv04    = :state_serv04,
                    state_serv05    = :state_serv05,
                    counties_serv01 = :counties_serv01,
                    counties_serv02 = :counties_serv02,
                    counties_serv03 = :counties_serv03,
                    counties_serv04 = :counties_serv04,
                    counties_serv05 = :counties_serv05";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'       => $broker_id,
                ':status'          => $status,
                ':type'            => $type,
                ':first_name'      => $first_name,
                ':last_name'       => $last_name,
                ':about_me'        => $about_me,
                ':cell'            => $cell,
                ':agent_email'     => $agent_email,
                ':agent_telephone' => $agent_telephone,
                ':address1'        => $address1,
                ':address2'        => $address2,
                ':city'            => $city,
                ':state'           => $state,
                ':zip'             => $zip,
                ':affiliations'    => $affiliations,
                ':state_serv01'    => $state_serv01,
                ':state_serv02'    => $state_serv02,
                ':state_serv03'    => $state_serv03,
                ':state_serv04'    => $state_serv04,
                ':state_serv05'    => $state_serv05,
                ':counties_serv01' => $counties_serv01,
                ':counties_serv02' => $counties_serv02,
                ':counties_serv03' => $counties_serv03,
                ':counties_serv04' => $counties_serv04,
                ':counties_serv05' => $counties_serv05
            ];
            $result = $stmt->execute($parameters);

            return $result;

            // Get id of last insert (broker_agents.id)
            $broker_agent_id = $db->lastInsertId();  // ID of new agent for this broker
        }
        catch (PDOException $e)
        {
            echo "Error inserting new agent data into database" . $e->getMessage();
            exit();
        }
    }




    public static function updateAgent($agent_id, $broker_id)
    {
        // retrieve post data from form
        $first_name = ( isset($_POST['first_name']) )? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_POST['last_name']) )? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $status = ( isset($_POST['status']) )? filter_var($_POST['status'], FILTER_SANITIZE_STRING) : '';
        $type = ( isset($_POST['type']) )? filter_var($_POST['type'], FILTER_SANITIZE_STRING) : '';

        $about_me = ( isset($_POST['about_me']) )? $_POST['about_me'] : '';

        $cell = ( isset($_POST['cell']) )? filter_var($_POST['cell'], FILTER_SANITIZE_NUMBER_INT) : '';
        $agent_telephone = ( isset($_POST['agent_telephone']) )? filter_var($_POST['agent_telephone'], FILTER_SANITIZE_NUMBER_INT) : '';
        $agent_email = ( isset($_POST['agent_email']) )? filter_var($_POST['agent_email'], FILTER_SANITIZE_EMAIL) : '';
        $address1 = ( isset($_POST['agent_address1']) )? filter_var($_POST['agent_address1'], FILTER_SANITIZE_STRING): '';
        $address2 = ( isset($_POST['agent_address2']) )? filter_var($_POST['agent_address2'], FILTER_SANITIZE_STRING): '';
        $city = ( isset($_POST['agent_city']) )? filter_var($_POST['agent_city'], FILTER_SANITIZE_STRING): '';
        $state = ( isset($_POST['agent_state']) )? filter_var($_POST['agent_state'], FILTER_SANITIZE_STRING): '';
        $zip = ( isset($_POST['agent_zip']) )? filter_var($_POST['agent_zip'], FILTER_SANITIZE_STRING): '';

        $affiliations = ( isset($_POST['affiliations']) )? $_POST['affiliations'] : '';

        $state_serv01 = ( isset($_POST['state01']) )? filter_var($_POST['state01'], FILTER_SANITIZE_STRING): '';
        $state_serv02 = ( isset($_POST['state02']) )? filter_var($_POST['state02'], FILTER_SANITIZE_STRING): '';
        $state_serv03 = ( isset($_POST['state03']) )? filter_var($_POST['state03'], FILTER_SANITIZE_STRING): '';
        $state_serv04 = ( isset($_POST['state04']) )? filter_var($_POST['state04'], FILTER_SANITIZE_STRING): '';
        $state_serv05 = ( isset($_POST['state05']) )? filter_var($_POST['state05'], FILTER_SANITIZE_STRING): '';

        $counties_state01 = ( isset($_POST['counties_state01']) )? filter_var($_POST['counties_state01'], FILTER_SANITIZE_STRING): '';
        $counties_state02 = ( isset($_POST['counties_state02']) )? filter_var($_POST['counties_state02'], FILTER_SANITIZE_STRING): '';
        $counties_state03 = ( isset($_POST['counties_state03']) )? filter_var($_POST['counties_state03'], FILTER_SANITIZE_STRING): '';
        $counties_state04 = ( isset($_POST['counties_state04']) )? filter_var($_POST['counties_state04'], FILTER_SANITIZE_STRING): '';
        $counties_state05 = ( isset($_POST['counties_state05']) )? filter_var($_POST['counties_state05'], FILTER_SANITIZE_STRING): '';

        // Retreive data from array if submitted
        $counties_serv01 = (isset($_POST['counties_serv01'])) ? $_POST['counties_serv01'] : '';
        $counties_serv02 = (isset($_POST['counties_serv02'])) ? $_POST['counties_serv02'] : '';
        $counties_serv03 = (isset($_POST['counties_serv03'])) ? $_POST['counties_serv03'] : '';
        $counties_serv04 = (isset($_POST['counties_serv04'])) ? $_POST['counties_serv04'] : '';
        $counties_serv05 = (isset($_POST['counties_serv05'])) ? $_POST['counties_serv05'] : '';

        // test
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // exit();

        if(is_array($counties_serv01) && !empty($counties_serv01))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv01 = implode(', ', $counties_serv01);
        }
        else
        {
            $counties_serv01 = $counties_state01;
        }


        if(is_array($counties_serv02) && !empty($counties_serv02))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv02 = implode(', ', $counties_serv02);
        }
        else
        {
            $counties_serv02 = $counties_state02;
        }


        if(is_array($counties_serv03) && !empty($counties_serv03))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv03 = implode(', ', $counties_serv03);
        }
        else
        {
            $counties_serv03 = $counties_state03;
        }


        if(is_array($counties_serv04) && !empty($counties_serv04))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv04 = implode(', ', $counties_serv04);
        }
        else
        {
            $counties_serv04 = $counties_state04;
        }


        if(is_array($counties_serv05) && !empty($counties_serv05))
        {
            // Implode $counties_served array into comma separated string
            $counties_serv05 = implode(', ', $counties_serv05);
        }
        else
        {
            $counties_serv05 = $counties_state05;
        }



        // establish db connection
        $db = static::getDB();

        // test
        // echo '$id => ' . $id . '<br>';
        // echo '$first_name => ' . $first_name . '<br>';
        // echo '$last_name => ' . $last_name . '<br>';
        // echo '$about_me => ' . $about_me . '<br>';
        // echo '$cell => ' . $cell . '<br>';
        // echo '$agent_email => ' . $agent_email . '<br>';
        // echo '$affiliations => ' . $affiliations . '<br>';
        // echo '$stmttates_served => ' . $stmttates_served . '<br>';
        // echo '$counties_served => ' . $counties_served . '<br>';
        // echo '$_FILES[\'profile_photo\'][\'tmp_name\'] => ' . $_FILES['profile_photo']['tmp_name'] . '<br>';
        // exit();

        // Check if profile photo was uploaded; if true, process
        if(!empty($_FILES['profile_photo']['tmp_name'])){

          // Assign target directory to variable
          $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_profile_photos/';

          if($_SERVER['SERVER_NAME'] != 'localhost')
          {
            // path for live server
            // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
            $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_profile_photos/';
          }
          else
          {
            // path for local machine
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_profile_photos/';
          }

            // test
            // echo '$target_dir: ' . $target_dir . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['profile_photo']['name'];
            $file_tmp = $_FILES['profile_photo']['tmp_name'];
            $file_type = $_FILES['profile_photo']['type'];
            $file_size = $_FILES['profile_photo']['size'];
            $file_err_msg = $_FILES['profile_photo']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix for broker & listing specific image identification
            $prefix = $broker_id.'-' . $agent_id . '-' .time(). '-';


            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Attach prefix to file name so server & database table match
            $file_name = $prefix . $file_name;

            // Check if file already exists
            if(file_exists($target_dir . $file_name))
            {
                $upload_ok = 0;
                $errMsg = "Sorry, profile photo file already exists. Please
                      select a different file or rename file and try again.";
                include 'includes/error.html.php';
                exit();
            }
            // Check if file size < 2 MB
            if($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp);
                $errMsg = 'File must be less than 2 Megabytes to upload.';
                include 'includes/error.html.php';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp);
                $errMsg = 'Image must be gif, jpg, jpeg, or png to upload.';
                include 'includes/error.html.php';
                exit();
            }
            // Check for any errors
            if($file_err_msg == 1)
            {
                $upload_ok = 0;
                $errMsg = 'Error uploading file. Please try again.';
                include 'includes/error.html.php';
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
                    $errMsg = 'File not uploaded. Please try again.';
                    include 'includes/error.html.php';
                    exit();
                }
            }
            else
            {
                $errMsg = 'File not uploaded. Please try again.';
                include 'includes/error.html.php';
                exit();
            }
        }

        /* - - - - - - - - -  Update with photo  - - - - - - - - - - - - - - - */

        // Perform update based on file being uploaded or not
        if( (isset($file_name)) && ($file_name != '') )
        {

            // Update broker_agents table with uploaded profile photo file
            try
            {
                $sql = "UPDATE broker_agents SET
                        first_name      = :first_name,
                        last_name       = :last_name,
                        status          = :status,
                        type            = :type,
                        agent_email     = :agent_email,
                        cell            = :cell,
                        agent_telephone = :agent_telephone,
                        address1        = :address1,
                        address2        = :address2,
                        city            = :city,
                        state           = :state,
                        zip             = :zip,
                        about_me        = :about_me,
                        profile_photo   = :profile_photo,
                        affiliations    = :affiliations,
                        state_serv01    = :state_serv01,
                        state_serv02    = :state_serv02,
                        state_serv03    = :state_serv03,
                        state_serv04    = :state_serv04,
                        state_serv05    = :state_serv05,
                        counties_serv01 = :counties_serv01,
                        counties_serv02 = :counties_serv02,
                        counties_serv03 = :counties_serv03,
                        counties_serv04 = :counties_serv04,
                        counties_serv05 = :counties_serv05
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id'              => $agent_id,
                    ':first_name'      => $first_name,
                    ':last_name'       => $last_name,
                    ':status'          => $status,
                    ':type'            => $type,
                    ':agent_email'     => $agent_email,
                    ':cell'            => $cell,
                    ':agent_telephone' => $agent_telephone,
                    ':address1'        => $address1,
                    ':address2'        => $address2,
                    ':city'            => $city,
                    ':state'           => $state,
                    ':zip'             => $zip,
                    ':about_me'        => $about_me,
                    ':profile_photo'   => $file_name,
                    ':affiliations'    => $affiliations,
                    ':state_serv01'    => $state_serv01,
                    ':state_serv02'    => $state_serv02,
                    ':state_serv03'    => $state_serv03,
                    ':state_serv04'    => $state_serv04,
                    ':state_serv05'    => $state_serv05,
                    ':counties_serv01' => $counties_serv01,
                    ':counties_serv02' => $counties_serv02,
                    ':counties_serv03' => $counties_serv03,
                    ':counties_serv04' => $counties_serv04,
                    ':counties_serv05' => $counties_serv05
                ];
                $result = $stmt->execute($parameters);

                return $result;
            }
            catch (PDOException $e)
            {
                echo "Error updating (w/ profile photo) database " . $e->getMessage();
                exit();
            }
        }




  /* - - - - - - - - - - - - -  No photo  - - - - - - - - - - - - - - -  */

        if( !isset($file_name) || $file_name == '' )
        {
            //  test
            //  echo 'Success!<br>';
            //  echo $counties_serv01.'<br>';
            //  echo $counties_serv02.'<br>';
            //  echo $counties_serv03.'<br>';
            //  echo $counties_serv04.'<br>';
            //  echo $counties_serv05.'<br>';
            //  exit();

            // Update broker_agents table WITHOUT uploaded profile photo file
            try
            {
                $sql = "UPDATE broker_agents SET
                        first_name      = :first_name,
                        last_name       = :last_name,
                        status          = :status,
                        type            = :type,
                        agent_email     = :agent_email,
                        cell            = :cell,
                        agent_telephone = :agent_telephone,
                        address1        = :address1,
                        address2        = :address2,
                        city            = :city,
                        state           = :state,
                        zip             = :zip,
                        about_me        = :about_me,
                        affiliations    = :affiliations,
                        state_serv01    = :state_serv01,
                        state_serv02    = :state_serv02,
                        state_serv03    = :state_serv03,
                        state_serv04    = :state_serv04,
                        state_serv05    = :state_serv05,
                        counties_serv01 = :counties_serv01,
                        counties_serv02 = :counties_serv02,
                        counties_serv03 = :counties_serv03,
                        counties_serv04 = :counties_serv04,
                        counties_serv05 = :counties_serv05
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':id'              => $agent_id,
                    ':first_name'      => $first_name,
                    ':last_name'       => $last_name,
                    ':status'          => $status,
                    ':type'            => $type,
                    ':agent_email'     => $agent_email,
                    ':cell'            => $cell,
                    ':agent_telephone' => $agent_telephone,
                    ':address1'        => $address1,
                    ':address2'        => $address2,
                    ':city'            => $city,
                    ':state'           => $state,
                    ':zip'             => $zip,
                    ':about_me'        => $about_me,
                    ':affiliations'    => $affiliations,
                    ':state_serv01'    => $state_serv01,
                    ':state_serv02'    => $state_serv02,
                    ':state_serv03'    => $state_serv03,
                    ':state_serv04'    => $state_serv04,
                    ':state_serv05'    => $state_serv05,
                    ':counties_serv01' => $counties_serv01,
                    ':counties_serv02' => $counties_serv02,
                    ':counties_serv03' => $counties_serv03,
                    ':counties_serv04' => $counties_serv04,
                    ':counties_serv05' => $counties_serv05
                ];
                $result = $stmt->execute($parameters);

                return true;
            }
            catch (PDOException $e)
            {
                echo "Error updating database " . $e->getMessage();
                exit();
            }
        }
    }




    public static function getProfilePhotoName($agent_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            // retrieve profile photo file name from db
            $sql = "SELECT profile_photo
                    FROM   broker_agents
                    WHERE  id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $agent_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($result);
            // echo '</pre>';
            // exit();

            // store value in variable
            $profile_photo = $result->profile_photo;

            // return value to brokers controller
            return $profile_photo;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function deleteProfilePhoto($profile_photo)
    {
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

        // assign target directory + photo name based on server location
        $image = $target_dir . $profile_photo;

        // test
        // echo '$image: ' . $image . '<br>';
        // exit();
        // if(file_exists($image))
        // {
        //    echo "File found!<br>";
        // } else {
        //    echo "File not found.";
        // }
        // exit();

        // Check if image exists; if true delete image
        if(file_exists($image))
        {
            unlink($image);
            return true;
        }
        else
        {
            return false;
        }
    }




    public static function deleteAgent($agent_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM broker_agents WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $agent_id
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




    public static function findExperts($expert_category, $state, $county, $orderby)
    {
        if($expert_category == '')
        {
            echo '<script>alert("Please select Expert type")</script>';
            echo '<script>window.location.href="/find-a-business-expert"</script>';
        }
        // business brokers
        if($expert_category == 1)
        {
          // business broker & both
          $WHERE = "WHERE broker_agents.type IN (1, 3)";
        }
        // comm real estate brokers
        if($expert_category == 2)
        {
          // real estate brokers & both
          $WHERE = "WHERE broker_agents.type IN (2, 3)";
        }
        // both
        if($expert_category == 3)
        {
          // both
          $WHERE = "WHERE broker_agents.type = " . "'$expert_category'";
        }

        // no state selected (default = 'all states' -- no filter)
        if($state == 'all')
        {
          $state = '';
        }
        else
        {
          // state selected -- search 1st state field for match
           $state = "AND state_serv01 = " . "'$state'";
        }
        // no county selecte (default = 'all counties' -- no filter)
        if($county == 'all')
        {
          $county = '';
        }
        else
        {
            // county selected -- search 1st county field for match
            $county = "AND counties_serv01 LIKE " . "'%$county%'";
        }
        // order results according to $orderby param/argument in function call
        if($orderby != null)
        {
            $orderby = 'ORDER BY ' . $orderby;
        }

        // test
        // echo '$expert_category: ' . $expert_category . '<br>';
        // echo '$state: ' . $state . '<br>';
        // echo '$county: ' . $county . '<br>';
        // echo '$orderby: ' . $orderby . '<br>';
        // exit();

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT brokers.broker_id, brokers.type,
                    brokers.first_name as broker_first_name,
                    brokers.last_name as broker_last_name,
                    brokers.broker_email, brokers.address1,
                    brokers.address2, brokers.city, brokers.state, brokers.zip,
                    brokers.telephone, brokers.fax, brokers.company_name,
                    brokers.company_logo, brokers.company_bio, brokers.services,
                    brokers.website,
                    broker_agents.id as agent_id, broker_agents.status,
                    broker_agents.type as broker_agent_type,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone,
                    broker_agents.cell as agent_cell,
                    broker_agents.address1 as agent_address1,
                    broker_agents.address2 as agent_address2,
                    broker_agents.city as agent_city,
                    broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip, broker_agents.about_me,
                    broker_agents.profile_photo, broker_agents.affiliations,
                    broker_agents.state_serv01, broker_agents.state_serv02,
                    broker_agents.state_serv03, broker_agents.state_serv04,
                    broker_agents.state_serv05,
                    broker_agents.counties_serv01,
                    broker_agents.counties_serv02, broker_agents.counties_serv03,
                    broker_agents.counties_serv04, broker_agents.counties_serv05
                    FROM brokers
                    INNER JOIN broker_agents
                    ON brokers.broker_id = broker_agents.broker_id
                    $WHERE
                    $state
                    $county
                    -- AND counties_serv01 LIKE '%$county%'
                    -- OR state_serv02 = '$state'
                    -- AND counties_serv02 LIKE '%$county%'
                    -- OR state_serv03 = '$state'
                    -- AND counties_serv03 LIKE '%$county%'
                    -- OR state_serv04 = '$state'
                    -- AND counties_serv04 LIKE '%$county%'
                    -- OR state_serv05 = '$state'
                    -- AND counties_serv05 LIKE '%$county%'
                    $orderby";
              $stmt = $db->prepare($sql);
              $stmt->execute();

              $experts = $stmt->fetchAll(PDO::FETCH_OBJ);

              // return object to Search Controller
              return $experts;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    public static function getRealtyBrokerAgents($broker_id, $type, $orderby, $limit)
    //$limit=null, $broker_id, $type=['2','3'], $orderby = 'broker_agents.last_name'
    {
        if($type != null)
        {
            // implode array elements into string
            $types = implode(',', $type);

            // store query in variable
            $type = "AND type IN ($types)";
        }
        else
        {
            $type = '';
        }

        // echo $types; exit();

        if($orderby != null)
        {
            $orderby = 'ORDER BY ' . $orderby;
        }

        if($limit != null)
        {
            $limit = 'LIMIT ' . $limit;
        }


        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT broker_agents.id as agent_id, broker_agents.status,
                    broker_agents.type,
                    broker_agents.first_name as agent_first_name,
                    broker_agents.last_name as agent_last_name,
                    broker_agents.agent_email,
                    broker_agents.agent_telephone,
                    broker_agents.cell as agent_cell,
                    broker_agents.address1 as agent_address1,
                    broker_agents.address2 as agent_address2,
                    broker_agents.city as agent_city,
                    broker_agents.state as agent_state,
                    broker_agents.zip as agent_zip, broker_agents.about_me,
                    broker_agents.profile_photo, broker_agents.affiliations,
                    broker_agents.state_serv01,broker_agents.state_serv02,
                    broker_agents.state_serv03, broker_agents.state_serv04,
                    broker_agents.state_serv05,
                    broker_agents.counties_serv01,
                    broker_agents.counties_serv02, broker_agents.counties_serv03,
                    broker_agents.counties_serv04, broker_agents.counties_serv05
                    FROM broker_agents
                    WHERE broker_id = :broker_id
                    $type
                    $orderby
                    $limit";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id,
            ];
            $stmt->execute($parameters);
            $agents = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return object to Admin/Brokers Controller
            return $agents;
        }
        catch(PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }
}
