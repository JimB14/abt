<?php

namespace App\Models;

use PDO;
use \App\Config;


class Broker extends \Core\Model
{

    public static function getBrokerDetails($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM brokers
                    WHERE broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);
            $broker = $stmt->fetch(PDO::FETCH_OBJ);

            return $broker;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * retrieves broker data with user ID
     *
     * @param  Integer  $user_id  The user's ID
     * @return Object             The broker's data
     */
    public static function getBrokerByUserId($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM brokers WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':user_id' => $user_id
            ];
            $stmt->execute($parameters);
            $broker = $stmt->fetch(PDO::FETCH_OBJ);

            return $broker;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getBrokerID($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT broker_id FROM brokers WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':user_id' => $user_id
            ];
            $stmt->execute($parameters);
            $results = $stmt->fetch(PDO::FETCH_OBJ);

            $broker_id = $results->broker_id;

            return $broker_id;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getBrokerCompanyName($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT company_name FROM brokers
                    WHERE broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $broker_company_name = $result['company_name'];

            return $broker_company_name;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function getBrokerCompanyType($broker_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT type FROM brokers
                    WHERE broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id' => $broker_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $broker_type = $result['type'];

            return $broker_type;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateCompanyProfile($broker_id)
    {
        // From form fields @admin/edit-company-profile-form.html.php (update `brokers` table with these values)
        $company_name = ( isset($_POST['company_name']) ) ? filter_var($_POST['company_name'], FILTER_SANITIZE_STRING) : '';
        $type = ( isset($_POST['type']) ) ? filter_var($_POST['type'], FILTER_SANITIZE_STRING) : '';
        $address1 = ( isset($_POST['address1']) ) ? filter_var($_POST['address1'], FILTER_SANITIZE_STRING) : '';
        $address2 = ( isset($_POST['address2']) ) ? filter_var($_POST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';
        $telephone = ( isset($_POST['telephone']) ) ? filter_var($_POST['telephone'], FILTER_SANITIZE_STRING) : '';
        $fax = ( isset($_POST['fax']) ) ? filter_var($_POST['fax'], FILTER_SANITIZE_STRING) : '';

        $company_bio = ( isset($_POST['company_bio']) ) ? $_POST['company_bio'] : '';
        $services = ( isset($_POST['services']) ) ? $_POST['services'] : '';

        $website = ( isset($_POST['website']) ) ? filter_var($_POST['website']) : '';
        $first_name = ( isset($_POST['first_name']) ) ? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_POST['last_name']) ) ? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $title = ( isset($_POST['title']) ) ? filter_var($_POST['title'], FILTER_SANITIZE_STRING) : '';
        $broker_email = ( isset($_POST['broker_email']) ) ? filter_var($_POST['broker_email'], FILTER_SANITIZE_EMAIL) : '';



        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE brokers SET
                    first_name      = :first_name,
                    last_name       = :last_name,
                    broker_email    = :broker_email,
                    title           = :title,
                    address1        = :address1,
                    address2        = :address2,
                    city            = :city,
                    state           = :state,
                    zip             = :zip,
                    telephone       = :telephone,
                    fax             = :fax,
                    services        = :services,
                    company_name    = :company_name,
                    type            = :type,
                    company_bio     = :company_bio,
                    website         = :website
                    WHERE broker_id = :broker_id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':broker_id'    => $broker_id,
                ':first_name'   => $first_name,
                ':last_name'    => $last_name,
                ':broker_email' => $broker_email,
                ':title'        => $title,
                ':address1'     => $address1,
                ':address2'     => $address2,
                ':city'         => $city,
                ':state'        => $state,
                ':zip'          => $zip,
                ':telephone'    => $telephone,
                ':fax'          => $fax,
                ':services'     => $services,
                ':company_name' => $company_name,
                ':type'         => $type,
                ':company_bio'  => $company_bio,
                ':website'      => $website
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo "Error updating brokers table database" . $e->getMessage();
            exit();
        }
    }





    public static function updateCompanyBrokerPhoto($broker_id)
    {
        // upload profile photo to server
        if(!empty($_FILES['broker_photo']['tmp_name']))
        {
            // Assign target directory based on server
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


            $target_file = $target_dir . $_FILES['broker_photo']['name'];

            // test - great test!
            // echo '$target_dir: ' . $target_dir . '<br>';
            // echo '$target_file: ' . $target_file . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['broker_photo']['name'];
            $file_tmp_loc = $_FILES['broker_photo']['tmp_name'];
            $file_type = $_FILES['broker_photo']['type'];
            $file_size = $_FILES['broker_photo']['size'];
            $file_err_msg = $_FILES['broker_photo']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);



            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Check if file already exists
            if (file_exists($target_file))
            {
                $upload_ok = 0;
                echo "Sorry, photo file already exists. Please select a
                      different file or rename file and try again.";
                exit();
            }

            // Check if file size < 2 MB
            if($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'File must be less than 2 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
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
                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp_loc, $target_file);

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


            // UPDATE broker_agents table with uploaded photo file
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE brokers SET
                        broker_photo    = :broker_photo
                        WHERE broker_id = :broker_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':broker_id'    => $broker_id,
                    ':broker_photo' => $file_name
                ];
                $result = $stmt->execute($parameters);

                return $result;
            }
            catch (PDOException $e)
            {
                echo "Error updating broker_agents (w/ photo) table in database " . $e->getMessage();
                exit();
            }
        }
    }




    public static function updateCompanyLogo($broker_id)
    {
        if(!empty($_FILES['company_logo']['tmp_name']))
        {
            // Assign target directory based on server
            if($_SERVER['SERVER_NAME'] != 'localhost')
            {
              // path for live server
              // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
              $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_company_logos/';
            }
            else
            {
              // path for local machine
              $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_company_logos/';
            }

            $target_file = $target_dir . $_FILES['company_logo']['name'];

            // test - great test!
            // echo '$target_dir: ' . $target_dir . '<br>';
            // echo '$target_file: ' . $target_file . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $company_logo_file = $_FILES['company_logo']['name'];
            $file_tmp_loc = $_FILES['company_logo']['tmp_name'];
            $file_type = $_FILES['company_logo']['type'];
            $file_size = $_FILES['company_logo']['size'];
            $file_err_msg = $_FILES['company_logo']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $company_logo_file);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Check if file already exists
            if (file_exists($target_file))
            {
                $upload_ok = 0;
                echo "Sorry, company logo file already exists. Please rename file and try again.";
                exit();
            }

            // Check if file size < 2 MB
            if($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'File must be less than 2 Megabytes to upload.';include 'includes/error.html.php';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if(!preg_match("/\.(gif|jpg|jpeg|png)$/i", $company_logo_file))
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
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
                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp_loc, $target_file);

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

            // Update brokers table with uploaded company logo file
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "UPDATE brokers SET
                        company_logo    = :company_logo
                        WHERE broker_id = :broker_id";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':broker_id'    => $broker_id,
                    ':company_logo' => $company_logo_file
                ];
                $result = $stmt->execute($parameters);

                return $result;
            }
            catch (PDOException $e)
            {
                echo "Error updating brokers (w/ logo) table: " . $e->getMessage();
                exit();
            }
        }
        else
        {
            return false;
        }
    }




    public static function addNewBroker($user_id)
    {
        // Retrieve post variables, sanitize and store in local variables
        $first_name = ( isset($_POST['first_name']) ) ? filter_var($_POST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = ( isset($_POST['last_name']) ) ? filter_var($_POST['last_name'], FILTER_SANITIZE_STRING) : '';
        $title = ( isset($_POST['title']) ) ? filter_var($_POST['title'], FILTER_SANITIZE_EMAIL) : '';
        $broker_email = ( isset($_POST['broker_email']) ) ? filter_var($_POST['broker_email'], FILTER_SANITIZE_EMAIL) : '';
        $broker_cell = ( isset($_POST['broker_cell']) ) ? filter_var($_POST['broker_cell'], FILTER_SANITIZE_EMAIL) : '';
        $address1 = ( isset($_POST['address1']) ) ? filter_var($_POST['address1'], FILTER_SANITIZE_STRING) : '';
        $address2 = ( isset($_POST['address2']) ) ? filter_var($_POST['address2'], FILTER_SANITIZE_STRING) : '';
        $city = ( isset($_POST['city']) ) ? filter_var($_POST['city'], FILTER_SANITIZE_STRING) : '';
        $state = ( isset($_POST['state']) ) ? filter_var($_POST['state'], FILTER_SANITIZE_STRING) : '';
        $zip = ( isset($_POST['zip']) ) ? filter_var($_POST['zip'], FILTER_SANITIZE_STRING) : '';
        $telephone = ( isset($_POST['telephone']) ) ? filter_var($_POST['telephone'], FILTER_SANITIZE_STRING) : '';
        $fax = ( isset($_POST['fax']) ) ? filter_var($_POST['fax'], FILTER_SANITIZE_STRING) : '';
        $company_name = ( isset($_POST['company_name']) ) ? filter_var($_POST['company_name'], FILTER_SANITIZE_STRING) : '';
        $type = ( isset($_POST['type']) ) ? filter_var($_POST['type'], FILTER_SANITIZE_NUMBER_INT) : '';
        $company_bio = ( isset($_POST['company_bio']) ) ? $_POST['company_bio'] : '';
        $services = ( isset($_POST['services']) ) ? $_POST['services'] : '';

        $website = ( isset($_POST['website']) ) ? filter_var($_POST['website'], FILTER_SANITIZE_STRING) : '';

        // test
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // exit();

        // Fallback validation code if user has disabled JavaScript
        if (
          ($first_name == '') || ($last_name == '') || ($title == '') || ($broker_email == '')
          || ($broker_cell == '') || ($company_name == '') || ($type == '') || ($address1 == '')
          || ($city == '') || ($state == '') || ($zip == '') || ($telephone == '')
          || ($services == '') || ($company_bio == '') || ($website == '')
        )
        {
          echo '<script>alert("All fields except Address2 and Fax are required.")</script>';
          exit();
        }

        // Check if company logo image was uploaded; if true, process
        if (!empty($_FILES['company_logo']['tmp_name']))
        {
            // Assign target directory based on server
            if($_SERVER['SERVER_NAME'] != 'localhost')
            {
              // path for live server
              // UPLOAD_PATH = '/home/pamska5/public_html/americanbiztrader.site/public'
              $target_dir = Config::UPLOAD_PATH . '/assets/images/uploaded_company_logos/';
            }
            else
            {
              // path for local machine
              $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/uploaded_company_logos/';
            }

            // create single variable
            //$target_file = $target_dir . $_FILES['company_logo']['name'];

            // test
            // echo '$server: ' . $server . '<br>';
            // echo '$target_dir: ' . $target_dir . '<br>';
            // echo '$target_file: ' . $target_file . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['company_logo']['name'];
            $file_tmp_loc = $_FILES['company_logo']['tmp_name'];
            $file_type = $_FILES['company_logo']['type'];
            $file_size = $_FILES['company_logo']['size'];
            $file_err_msg = $_FILES['company_logo']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix
            //$prefix = $user_id.'-';
            $prefix = $user_id.'-'.time().'-';


            /* - - - - -  Error handling  - - - - - - */
            $upload_ok = 1;

            // Check if file already exists
            if ( file_exists($target_dir . $prefix . $file_name) )
            {
                echo "Sorry, company logo file already exists. Please rename
                file and try again.";
                exit();
            }
            // Check if file size < 2 MB
            if ($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'File must be less than 2 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if (!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'Image must be gif, jpg, jpeg, or to upload.';
                exit();
            }
            // Check for any errors
            if ($file_err_msg == 1)
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
                $move_result = move_uploaded_file($file_tmp_loc, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp_loc);
                    echo 'File not uploaded. Please try again.';
                    exit();
                }

                // change file_name for db insertion
                $company_logo = $file_name;
            }
        }
        else
        {
            echo "Company logo required.";
            exit();
        }




        /* - - - - - -  Broker photo - - - - - - - - */

        // Check if broker photo was uploaded; if true, process
        if (!empty($_FILES['broker_photo']['tmp_name']))
        {
            // Assign target directory based on server
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

            // create single variable
            //$target_file = $target_dir . $_FILES['broker_photo']['name'];

            // test
            // echo '$server: ' . $server . '<br>';
            // echo '$target_dir: ' . $target_dir . '<br>';
            // echo '$target_file: ' . $target_file . '<br>';
            // exit();

            // Access $_FILES global array for uploaded file
            $file_name = $_FILES['broker_photo']['name'];
            $file_tmp_loc = $_FILES['broker_photo']['tmp_name'];
            $file_type = $_FILES['broker_photo']['type'];
            $file_size = $_FILES['broker_photo']['size'];
            $file_err_msg = $_FILES['broker_photo']['error'];

            // Separate file name into an array by the dot
            $kaboom = explode(".", $file_name);

            // Assign last element of array to file_extension variable (in case file has more than one dot)
            $file_extension = end($kaboom);

            // Assign value to prefix
            //$prefix = $user_id.'-';
            $prefix = $user_id.'-'.time().'-';


            /* - - - - -  Error handling  - - - - - - */

            $upload_ok = 1;

            // Check if file already exists
            if ( file_exists($target_dir . $prefix . $file_name) )
            {
                $upload_ok = 0;
                echo "Sorry, broker photo file already exists. Please rename
                file and try again.";
                exit();
            }
            // Check if file size < 2 MB
            if ($file_size > 2097152)
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'File must be less than 2 Megabytes to upload.';
                exit();
            }
            // Check if file is gif, jpg, jpeg or png
            if (!preg_match("/\.(gif|jpg|jpeg|png)$/i", $file_name))
            {
                $upload_ok = 0;
                unlink($file_tmp_loc);
                echo 'Image must be gif, jpg, jpeg, or to upload.';
                exit();
            }
            // Check for any errors
            if ($file_err_msg == 1)
            {
                $upload_ok = 0;
                echo 'Error uploading file. Please try again.';
                exit();
            }

            if($upload_ok = 1)
            {
                // Attach prefix to file name so server & database table match
                $file_name = $prefix . $file_name;

                // Upload file to server into designated folder
                $move_result = move_uploaded_file($file_tmp_loc, $target_dir . $file_name);

                // Check for boolean result of move_uploaded_file()
                if ($move_result != true)
                {
                    unlink($file_tmp_loc);
                    echo 'File not uploaded. Please try again.';
                    exit();
                }

                // change file_name for db insertion
                $broker_photo = $file_name;

            }
        }
        else
        {
            echo "Profile photo required.";
            exit();
        }


        // insert new broker data, including company logo and broker photo
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "INSERT INTO brokers SET
                    user_id       = :user_id,
                    first_name    = :first_name,
                    last_name     = :last_name,
                    title         = :title,
                    broker_email  = :broker_email,
                    broker_cell   = :broker_cell,
                    broker_photo  = :broker_photo,
                    address1      = :address1,
                    address2      = :address2,
                    city          = :city,
                    state         = :state,
                    zip           = :zip,
                    telephone     = :telephone,
                    fax           = :fax,
                    company_name  = :company_name,
                    type          = :type,
                    company_logo  = :company_logo,
                    services      = :services,
                    company_bio   = :company_bio,
                    website       = :website";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':user_id'      => $user_id,
                ':first_name'   => $first_name,
                ':last_name'    => $last_name,
                ':title'        => $title,
                ':broker_email' => $broker_email,
                ':broker_cell'  => $broker_cell,
                ':broker_photo' => $broker_photo,
                ':address1'     => $address1,
                ':address2'     => $address2,
                ':city'         => $city,
                ':state'        => $state,
                ':zip'          => $zip,
                ':telephone'    => $telephone,
                ':fax'          => $fax,
                ':company_name' => $company_name,
                ':type'         => $type,
                ':company_logo' => $company_logo,
                ':services'     => $services,
                ':company_bio'  => $company_bio,
                ':website'      => $website
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo "Error updating data in database " . $e->getMessage();
            exit();
        }
    }


}
