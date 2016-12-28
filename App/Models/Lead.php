<?php

namespace App\Models;

use PDO;

/**
 * Lead model
 */
class Lead extends \Core\Model
{

    public static function setLeadData($lead_data)
    {
        // convert array elements into variables with values
        extract($lead_data);

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO leads SET
                    listing_id        = :listing_id,
                    broker_id         = :broker_id,
                    listing_agent_id  = :listing_agent_id,
                    agent_last_name   = :agent_last_name,
                    clients_id        = :clients_id,
                    type              = :type,
                    ad_title          = :ad_title,
                    asking_price      = :asking_price,
                    address           = :address,
                    address2          = :address2,
                    city              = :city,
                    state             = :state,
                    county            = :county,
                    zip               = :zip,
                    description       = :description,
                    first_name        = :first_name,
                    last_name         = :last_name,
                    telephone         = :telephone,
                    email             = :email,
                    investment        = :investment,
                    time_frame        = :time_frame,
                    message           = :message";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':listing_id'       => $listing_id,
                ':broker_id'        => $broker_id,
                ':listing_agent_id' => $listing_agent_id,
                ':agent_last_name'  => $agent_last_name,
                ':clients_id'       => $clients_id,
                ':type'             => $type,
                ':ad_title'         => $ad_title,
                ':asking_price'     => $asking_price,
                ':address'          => $address,
                ':address2'         => $address2,
                ':city'             => $city,
                ':state'            => $state,
                ':county'           => $county,
                ':zip'              => $zip,
                ':description'      => $description,
                ':first_name'       => $first_name,
                ':last_name'        => $last_name,
                ':telephone'        => $telephone,
                ':email'            => $email,
                ':investment'       => $investment,
                ':time_frame'       => $time_frame,
                ':message'          => $message
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Realty Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getLeads($broker_id, $limit)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT* FROM leads
                    WHERE broker_id = :broker_id
                    ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'  => $broker_id
            ];
            $stmt->execute($parameters);
            $leads = $stmt->fetchAll(PDO::FETCH_OBJ);

            // return to Admin/Brokers Controller
            return $leads;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getLeadsBySearchCriteria($broker_id, $last_name, $clients_id, $limit)
    {
        if($limit != null)
        {
            $limit = 'LIMIT  ' . $limit;
        }
        if($last_name != null)
        {
            $last_name_for_view = $last_name;
            $last_name = "AND broker_agents.last_name LIKE '$last_name_for_view%'";
            $pagetitle = "Leads by last name: $last_name_for_view";
        }
        if($clients_id != null)
        {
            $clients_id_for_view = $clients_id;
            $clients_id = "AND leads.clients_id LIKE '$clients_id_for_view'";
            $pagetitle = "Leads by ID: $clients_id_for_view";
        }

        // execute query
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM leads
                    LEFT JOIN broker_agents
                    ON broker_agents.id = leads.listing_agent_id
                    LEFT JOIN brokers
                    ON brokers.broker_id = leads.broker_id
                    WHERE leads.broker_id = :broker_id
                    $last_name
                    $clients_id
                    ORDER BY leads.created_at  DESC
                    $limit";

            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);

            // store leads details in object
            $leads = $stmt->fetchAll(PDO::FETCH_OBJ);

            // store in associative array
            $results = [
                'leads'     => $leads,
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



    public static function deleteLead($id, $broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "DELETE FROM leads
                    WHERE id = :id
                    AND broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $id,
                ':broker_id'  => $broker_id
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

}
