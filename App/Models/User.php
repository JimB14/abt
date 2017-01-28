<?php

namespace App\Models;


use PDO;


class User extends \Core\Model
{

    public static function checkIfAvailable($email)
    {
        if($email == '' || strlen($email) < 3)
        {
          echo "Invalid email address";
          exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            $count = $stmt->rowCount();

            if ($count < 1)
            {
              return 'available';
            }
            else
            {
              return 'not available';
            }
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * adds new user to users table
     *
     * @return [type] [description]
     */
    public static function addNewUser()
    {
        // unset SESSION variable
        unset($_SESSION['registererror']);

        // create gatekeeper variable
        $okay = true;

        // retrieve 3  of 4 post items from form
        $email = isset($_REQUEST['email']) ? filter_var($_REQUEST['email'], FILTER_SANITIZE_EMAIL) : '';
        $first_name = isset($_REQUEST['first_name']) ? filter_var($_REQUEST['first_name'], FILTER_SANITIZE_STRING) : '';
        $last_name = isset($_REQUEST['last_name']) ? filter_var($_REQUEST['last_name'], FILTER_SANITIZE_STRING) : '';
        $company = isset($_REQUEST['company']) ? filter_var($_REQUEST['company'], FILTER_SANITIZE_STRING) : '';
        $user_ip = $_SERVER['REMOTE_ADDR'];

        if($first_name === '' || $last_name === '' || $email === '' || $company === '')
        {
            $_SESSION['registererror'] = '*All fields are required.';
            $okay = false;
            header("Location: /register");
            exit();
        }

        // test
        // check if data passing
        // echo '<pre>';
        // print_r($_REQUEST);
        // echo '</pre>';
        //exit();

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
        {
            $_SESSION['registererror'] = '*Please enter a valid email address.';
            $okay = false;
            header("Location: /register");
            exit();
        }

        $verify_email = isset($_REQUEST['verify_email']) ? filter_var($_REQUEST['verify_email'], FILTER_SANITIZE_EMAIL) : '';

         // check if emails match
         if($verify_email != $email)
         {
             $okay = false;
             $_SESSION['registererror'] = '*Emails do not match.';
             $okay = false;
             header("Location: /register");
             exit();
         }

         // retrieve password, sanitize, verify
         $password = isset($_REQUEST['password']) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';
         $verify_password = isset($_REQUEST['verify_password']) ? filter_var($_REQUEST['verify_password'], FILTER_SANITIZE_STRING) : '';

        // check if passwords match
        if($verify_password != $password)
        {
            $_SESSION['registererror'] = '*Passwords do not match';
            $okay = false;
            header("Location: /register");
            exit();
        }

        // hash password
        $pass = password_hash($password, PASSWORD_DEFAULT);

        // test
        // echo $first_name . '<br>';
        // echo $last_name . '<br>';
        // echo $email . '<br>';
        // echo $pass . '<br>';
        // echo $okay . '<br>';
        // exit();

        if($okay == true)
        {
            // establish db connection
            $db = static::getDB();

            // insert user data into users table
            try
            {
                $sql = "INSERT INTO users (first_name, last_name, company, email, pass, user_ip)
                        VALUES (:first_name, :last_name, :company, :email, :pass, :user_ip)";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':first_name' => $first_name,
                    ':last_name'  => $last_name,
                    ':company'    => $company,
                    ':email'      => $email,
                    ':pass'       => $pass,
                    ':user_ip'    => $user_ip
                ];
                $result = $stmt->execute($parameters);

                // get new user's id
                $user_id = $db->lastInsertId();

                // create token for validation email
                $token = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

                // store result, new user ID & token in array to return to
                // Register controller
                $results = [
                    'result'  => $result,
                    'user_id' => $user_id,
                    'token'   => $token
                ];

                // test
                // echo '<pre>';
                // print_r($results);
                // echo '</pre>';
                // exit();

                // return array to register controller
                return $results;
            }
            catch (PDOException $e)
            {
                $_SESSION['registererror'] = "Error adding user to database " . $e->getMessage();
                header("Location: /register");
                exit();
            }
        }
        else
        {
            $_SESSION['registererror'] = "Error during registration. Please try again.";
            header("Location: /register");
            exit();
        }
    }




    /**
     * gets User data
     *
     * @param  integer $user_id The user ID
     *
     * @return Object           The user data
     */
    public static function getUser($user_id)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $user_id
            ];
            $stmt->execute($parameters);

            // store user data in object
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Register controller
            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * gets User data by brokers ID
     *
     * @param  integer  $broker_id   The broker's ID
     * @return Object               The user data
     */
    public static function getUserByBrokerUserId($broker_user_id)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $stmt->execute($parameters);

            // store user data in object
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // return object to Siteadmin controller
            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates value in users.current after successful payment
     *
     * @param  integer $user_id  The user ID
     *
     * @return boolean
     */
    public static function updateCurrent($user_id, $current, $sub_amt, $max_agents)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "UPDATE users SET
                    current = :current,
                    sub_amt = :sub_amt,
                    max_agents = :max_agents
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $user_id,
                ':current'    => $current,
                ':sub_amt'    => $sub_amt,
                ':max_agents' => $max_agents
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Subscribe Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }



    /**
     * updates value in users account after cancellation
     *
     * @param  integer  $user_id  The user ID
     * @param  integer  $user_id  The payment status
     * @param  decimal  $user_id  The subscription amount
     * @param  integer  $user_id  The number of agents
     *
     * @return boolean
     */
    public static function updateUserAccount($user_id, $current, $sub_amt, $max_agents)
    {
        // establish db connection
        $db = static::getDB();

        try
        {
            $sql = "UPDATE users SET
                    current = :current,
                    sub_amt = :sub_amt,
                    max_agents = :max_agents
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $user_id,
                ':current'    => $current,
                ':sub_amt'    => $sub_amt,
                ':max_agents' => $max_agents
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Subscribe Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    /**
     * validates user credentials
     *
     * @param  string $email     The user's email
     * @param  string $password  The user's password
     *
     * @return boolean
     */
    public static function validateLoginCredentials($email, $password)
    {
        // clear variable for new values
        unset($_SESSION['loginerror']);

        // set gate-keeper to true
        $okay = true;

        // check if fields have length
        if($email == "" || $password == "")
        {
            $_SESSION['loginerror'] = 'Please enter login email and password.';
            $okay = false;
            header("Location: /login");
            exit();
        }

        // validate email
        if(filter_var($email, FILTER_SANITIZE_EMAIL === false))
        {
            $_SESSION['loginerror'] = 'Please enter a valid email address';
            $okay = false;
            header("Location: /login");
            exit();
        }

        if($okay)
        {
            // check if email exists & retrieve password
            try
            {
                // establish db connection
                $db = static::getDB();

                $sql = "SELECT * FROM users WHERE
                        email = :email
                        AND active = 1";
                $stmt = $db->prepare($sql);
                $parameters = [
                    ':email' => $email
                ];
                $stmt->execute($parameters);
                $user = $stmt->fetch(PDO::FETCH_OBJ);
            }
            catch (PDOException $e)
            {
                $_SESSION['loginerror'] = "Error checking credentials";
                header("Location: /login/index");
                exit();
            }
        }

        // check if email & active match found
        if(empty($user))
        {
            $_SESSION['loginerror'] = "User not found";
            header("Location: /login");
            exit();
        }

        // returning user verified
        if( (!empty($user)) && (password_verify($password, $user->pass)) )
        {
            // return $user object to Login controller
            return $user;
        }
        else
        {
            $_SESSION['loginerror'] = "Matching credentials not found.
            Please verify and try again.";
            header("Location: /login");
            exit();
        }
    }




    public static function updatePassword($user_id)
    {
        // retrieve post variables
        $current_password = (isset($_REQUEST['current_password'])) ? filter_var($_REQUEST['current_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_password = (isset($_REQUEST['confirm_password'])) ? filter_var($_REQUEST['confirm_password'], FILTER_SANITIZE_STRING) : '';
        $new_password = (isset($_REQUEST['new_password'])) ? filter_var($_REQUEST['new_password'], FILTER_SANITIZE_STRING) : '';
        $confirm_new_password = (isset($_REQUEST['confirm_new_password'])) ? filter_var($_REQUEST['confirm_new_password'], FILTER_SANITIZE_STRING) : '';

        if($current_password != $confirm_password)
        {
            echo "Error. Current passwords do not match.";
            exit();
        }
        if($new_password != $confirm_new_password)
        {
            echo "Error. New passwords do not match.";
            exit();
        }
        if(strlen($new_password) < 6)
        {
            echo "Error. Password must be at least 6 characters.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT pass FROM users WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $user_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            // retrieve hashed password
            $hash_pass = $result->pass;

            // verify match
            if(password_verify($current_password, $hash_pass))
            {
                // create hash of new password
                $new_password = password_hash($new_password, PASSWORD_DEFAULT);

                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "UPDATE users SET pass = :pass WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $parameters = [
                        ':pass' => $new_password,
                        ':id'   => $user_id
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
            else
            {
                echo "Error. Password match not found.";
                exit();
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function updateLoginEmail($user_id)
    {
        // retrieve post variables
        $current_login_email = (isset($_REQUEST['current_login_email'])) ? filter_var($_REQUEST['current_login_email'], FILTER_SANITIZE_EMAIL) : '';
        $new_login_email = (isset($_REQUEST['new_login_email'])) ? filter_var($_REQUEST['new_login_email'], FILTER_SANITIZE_EMAIL) : '';
        $confirm_new_login_email = (isset($_REQUEST['confirm_new_login_email'])) ? filter_var($_REQUEST['confirm_new_login_email'], FILTER_SANITIZE_EMAIL) : '';
        $password = (isset($_REQUEST['password'])) ? filter_var($_REQUEST['password'], FILTER_SANITIZE_STRING) : '';

        if($current_login_email == '')
        {
            echo "Error. Current login email required.";
            exit();
        }
        if($new_login_email != $confirm_new_login_email)
        {
            echo "Error. New email addresses do not match.";
            exit();
        }
        if(filter_var($new_login_email, FILTER_SANITIZE_EMAIL) === false)
        {
            echo "Error. Please enter valid email address.";
            exit();
        }

        if(filter_var($password, FILTER_SANITIZE_STRING) === false)
        {
            echo "Error. Please enter valid password.";
            exit();
        }

        // verify email/id match
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE id = :id
                    AND email = :email";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'    => $user_id,
                ':email' => $current_login_email
            ];
            $stmt->execute($parameters);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            if(password_verify($password, $user->pass))
            {
                try
                {
                    // establish db connection
                    $db = static::getDB();

                    $sql = "UPDATE users SET
                            email = :email
                            WHERE id = :id";
                    $stmt = $db->prepare($sql);
                    $parameters = [
                        ':email' => $new_login_email,
                        ':id'    => $user_id
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
            else
            {
                echo "Unable to verify user. Please try again.";
                exit();
            }
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function doesUserExist($email)
    {
        // Server side validation (HTML5 validation 'required' on input tag)
        if($email === '' || strlen($email) < 6){
            echo 'Please provide a valid email address';
            exit();
        }

        // check if email is in `users` table
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE email = :email
                    AND active = 1";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email' => $email
            ];
            $stmt->execute($parameters);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // return user object to Login Controller
            return $user;
        }
        catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }




    public static function insertTempPassword($id, $tmp_pass)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET tmp_pass = :tmp_pass
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':tmp_pass' => $tmp_pass,
                ':id'       => $id
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




    public static function loginUserWithTempPassword($email,$tmp_pass)
    {
        if($email == '' || $tmp_pass == '')
        {
            echo "Submitted data is invalid.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users
                    WHERE email = :email
                    AND tmp_pass = :tmp_pass";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':email'    => $email,
                ':tmp_pass' => $tmp_pass
            ];
            $stmt->execute($parameters);
            $user = $stmt->fetch(PDO::FETCH_OBJ);

            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function deleteTempPassword($id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET tmp_pass = null
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch(PDOException $e)
        {
            $e->getMessage();
            exit();
        }
    }




    public static function updateFirstLoginStatus($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    first_login = 0,
                    access_level = 2
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $user_id
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Register controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function storeSecurityAnswers($user_id)
    {
        // retrieve post variables
        $security1 = (isset($_REQUEST['security1'])) ? filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING) : '';
        $security2 = (isset($_REQUEST['security2'])) ? filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING) : '';
        $security3 = (isset($_REQUEST['security3'])) ? filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING) : '';

        // backup validation
        if(empty($security1) || empty($security2) || empty($security3))
        {
            echo "All fields required.";
            exit();
        }

        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    security1 = :security1,
                    security2 = :security2,
                    security3 = :security3
                    WHERE id  = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':security1' => $security1,
                ':security2' => $security2,
                ':security3' => $security3,
                ':id'        => $user_id
            ];
            $result = $stmt->execute($parameters);

            // return boolean to Register Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }




    public static function checkSecurityAnswers($user_id)
    {
        // retrieve form data
        $security1 = ( isset($_REQUEST['security1']) ) ? strtolower(filter_var($_REQUEST['security1'], FILTER_SANITIZE_STRING)) : '';
        $security2 = ( isset($_REQUEST['security2']) ) ? strtolower(filter_var($_REQUEST['security2'], FILTER_SANITIZE_STRING)) : '';
        $security3 = ( isset($_REQUEST['security3']) ) ? strtolower(filter_var($_REQUEST['security3'], FILTER_SANITIZE_STRING)) : '';

        // test
        // echo $security1 .'<br>';
        // echo $security2 .'<br>';
        // echo $security3 .'<br>';
        // exit();

        // check for values
        if($security1 == '' || $security2 == '' || $security3 == '')
        {
            echo "All fields required.";
            exit();
        }

        // check values against db
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM users WHERE
                    id = :id
                    AND security1 = :security1
                    AND security2 = :security2
                    AND security3 = :security3";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'        => $user_id,
                ':security1' => $security1,
                ':security2' => $security2,
                ':security3' => $security3
            ];
            $stmt->execute($parameters);

            $user = $stmt->fetch(PDO::FETCH_OBJ);

            // test
            // echo '<pre>';
            // print_r($user);
            // echo '</pre>';
            // exit();

            return $user;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * updates user's table after paying for new agents
     *
     * @param  Integer   $user_id      The user's ID
     * @param  Integer   $new_amount   New monthly billing amount
     * @param  Integer   $agents_added Number of agents added
     * @return boolean
     */
    public static function updateUserAfterAddingAgents($user_id, $new_max_agents, $returned_amount)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    max_agents = :max_agents,
                    sub_amt    = :sub_amt
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':max_agents' => $new_max_agents,
                ':sub_amt'    => $returned_amount,
                ':id'         => $user_id
            ];
            $result = $stmt->execute($parameters);

            // return to Controller
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * updates user's table after removing x number of agents
     *
     * @param  Integer   $user_id      The user's ID
     * @param  Integer   $new_amount   New monthly billing amount
     * @param  Integer   $agents_added Number of agents added
     * @return boolean
     */
    public static function updateUserAfterDeductingAgents($user_id, $new_max_agents, $returned_amount)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    max_agents = :max_agents,
                    sub_amt    = :sub_amt
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':max_agents' => $new_max_agents,
                ':sub_amt'    => $returned_amount,
                ':id'         => $user_id
            ];
            $result = $stmt->execute($parameters);

            // return to Controller
            return $result;
        }
        catch (PDOException $e)
        {
            echo $e-getMessage();
            exit();
        }
    }


    /**
     * updates max_agents field after new agent posted
     *
     * @param  Integer  $user_id   The user's ID
     * @return boolean
     */
    public static function updateMaxagents($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    max_agents = max_agents - 1
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id' => $user_id
            ];
            $result = $stmt->execute($parameters);

            return $result;
        }
        catch (PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * retrieves PROFILEID from `paypal_log`
     *
     * @param  Integer   $user_id   The user's ID
     * @return String               The user's PayPal PROFILEID
     */
    public static function getProfileId($user_id)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "SELECT * FROM paypal_log
                    WHERE user_id = :user_id
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':user_id' => $user_id
            ];
            $stmt->execute($parameters);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            // store profileid in variable
            $profileid = $result->PROFILEID;

            // return PROFILEID to Subscribe Controller
            return $profileid;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }


    /**
     * updates user data after reactivation
     *
     * @param  Integer  $user_id      The user's ID
     * @param  Integer  $current      The payment status
     * @param  Decimal  $sub_amt      The subscription amount
     * @param  Integer  $max_agents   The number of agents
     * @return boolean
     */
    public static function updateAfterReactivation($user_id, $current, $sub_amt, $max_agents)
    {
        try
        {
            // establish db connection
            $db = static::getDB();

            $sql = "UPDATE users SET
                    current = :current,
                    sub_amt = :sub_amt,
                    max_agents = :max_agents
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $parameters = [
                ':id'         => $user_id,
                ':current'    => $current,
                ':sub_amt'    => $sub_amt,
                ':max_agents' => $max_agents
            ];
            $result = $stmt->execute($parameters);

            // return booelan to Subscribe Controller
            return $result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage();
            exit();
        }
    }

}
