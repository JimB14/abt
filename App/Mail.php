<?php

namespace App;

use App\Config;


/**
 * Mail class
 *
 * PHP version 7.0
 */
class Mail
{
    public static function sendAccountVerificationEmail($token, $user_id, $email, $first_name)
    {
        /**
         *  PHPMailer
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // test
        // get_class($mail) . '<br>';
        // //exit;
        // echo $token . '<br>';
        // echo $user_id . '<br>';
        // echo $email . '<br>';
        // echo $first_name . '<br>';
        // exit();

        // resource: https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps

        //echo get_class($mail);
        $mail->isSMTP();
        $mail->Host     = Config::SMTP_HOST;
        $mail->Port     = Config::SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->CharSet = 'UTF-8';

        /**
         * Enable SMTP debug messages
         */
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send an email
         */
        // "From" and "To"
        $mail->setFrom('noreply@americanbiztrader.site', 'ABT'); // gmail
        // overrides this setting with SMTP_USER
        $mail->addAddress($email, $first_name);

        // multiple "To" addresses
        //$mail->addAddress('sales@webmediapartners.com', 'Jim');
        //$mail->addAddress('info@webmediapartners.com');

        // "Cc" addresses
        //$mail->addCC('jim.burns@webmediapartners.com', 'Jim');
        //$mail->addCC('jim.burns14@gmail.com');

        // "Bcc" address
        $mail->addBCC('dave.didion@americangymtrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        //$mail->addBCC('jim.burns@webmediapartners.com');

        // add different "Reply to" email address
        //$mail->addReplyTo('danat927@gmail.com', 'Dana');

        // sends email as HTML
        $mail->isHTML(true);

        // include plain text version (SPAM filters look for it)

        // add attachment (__FILE__ magic constant that returns full path & file name
        // of the script file); dirname() returns the path w/o file name
        //$mail->addAttachment(dirname(__FILE__) . '/assets/images/koala.jpg', 'newFileName.jpg');
        // add another attachment
        //$mail->addAttachment(dirname(__FILE__) . '/assets/images/penguins.jpg', 'anotherName.jpg');

        // Subject & body
        $mail->Subject = 'Activate Account';
        $mail->Body = '<h1 style="color:#0000FF;">Account Activation</h1>'
                    . '<h3>American Biz Trader</h3>'
                    . '<p>Please click the link below to verify your account.</p>'
                    . '<p>If you did not register or are receiving this in error, please delete. </p>'
                    . '<p><a href="http://americanbiztrader.site/register/verify-account?token='
                    . $token . '&amp;user_id=' . $user_id . '">Click here to activate your account.</a></p>';

        // embed image in email
        //$mail->AddEmbeddedImage(dirname(__FILE__) . '/assets/images/koala.jpg', 'koala');

        // alternative body (plain-text email)
        //$mail->AltBody = "Hello. \nThis is the body in plain text for non-HTML
        //mail clients"; // must use "" or \n will not work

        // send email
        if(!$mail->send())
        {
            echo 'Mailer error: ' . $mail->ErrorInfo;
            exit();
        }
        else {
            $result = true;

            return $result;
        }
    }



    /**
     * Emails ABT contact form data to specified email addresses
     *
     * @param  string $first_name The user's first name
     * @param  string $last_name  The user's last name
     * @param  string $telephone  The user's telephone
     * @param  string $email      The user's email address
     * @param  string $message    The user's message
     *
     * @return boolean
     */
    public static function mailContactFormData($first_name, $last_name, $telephone, $email, $message)
    {
        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('contact@americanbiztrader.com', $first_name . ' ' . $last_name);
        $mail->addAddress('dave.didion@americangymtrader.com', 'ABT');
        $mail->addCC('sales@americanbiztrader.com');
        // $mail->addCC('jim.burns@webmediapartners.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Message via contact form';
        $mail->Body = '<h2 style="color:#0000FF;">Message from website contact form</h2>'
                    . '<p>Name: ' . $first_name . ' ' . $last_name . '</p>'
                    . '<p>Telephone: ' . $telephone . '</p>'
                    . '<p>Email: ' . $email . '</p>'
                    . '<p>Message: ' . $message . '</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }




    /**
     * Sends notification email that user posted a testimonial
     *
     * @param  INT $id        The testimonial id
     * @param  INT $user_id   The user's id
     * @param  string $user_full_name   The user's first + last name
     * @param  string $token          Unique string for matching
     * @param  string $title          Testimonial title
     * @param  string $testimonial    Testimonial content
     *
     * @return boolean
     */
    public static function sendNewTestimonialNotification($id, $user_id, $user_full_name, $token, $title, $testimonial)
    {
        // echo "Connected to sendNewTestimonialNotification method in Mail class!<br>";
        // echo $id . "<br>";
        // echo $user_id . "<br>";
        // echo $user_full_name . "<br>";
        // echo $token . "<br>";
        // echo $title . "<br>";
        // echo $testimonial . "<br>";
        // exit();

        // create new instance of PHPMailer object
        $mail = new \PHPMailer();

        $mail->isSMTP();
        $mail->Host       =  Config::SMTP_SEND_MAIL_INTERNALLY_HOST;
        //$mail->Port       =  Config::SMTP_PORT;
        $mail->SMTPAuth   =  true;
        $mail->Username   =  Config::SMTP_USER;
        $mail->Password   =  Config::SMTP_PASSWORD;
        $mail->SMTPSecure =  'tls';
        $mail->CharSet    =  'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Enable SMTP debug messages
         */
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        /**
         * Send an email
         */
        // "From" and "To"
        $mail->setFrom('sales@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress('sales@americanbiztrader.com', 'Dave');
        //$mail->addAddress('dave.didion@americangymtrader.com', 'Dave');
        //$mail->addCC('wesleyajohnston@gmail.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');
        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'New testimonial';
        $mail->Body = '<h1 style="color:#0000FF;">New Testimonial</h1>'
                    . '<p>A registered user just submitted a testimonial. The details are below.</p>'
                    . '<h3>'
                    . 'User: ' . $user_full_name
                    . '<br>'
                    . 'Title: ' . $title
                    . '<br>'
                    . 'Testimonial:'
                    . '</h3>'
                    . '<p>'
                    .  $testimonial
                    . '</p>'
                    . '<h3>'
                    . 'To publish this testimonial on your website, click the link below.'
                    . '<br>'
                    . 'If you do not wish to publish this testimonial, no action is required.'
                    . '</h3>'
                    . '<p>By clicking the link below:</p>'
                    . '<ol>'
                    . '<li>The testmonial will be published to the website</li>'
                    . '<li>A thank you email will be sent to the testimonial author&#39;s email address</li>'
                    . '<li>A copy of this &quot;thank you&quot; email will be sent to you (website owner or designee)</li>'
                    . '</ol>'
                    . '<p><a href="http://americanbiztrader.site/testimonials/publishTestimonial?token='
                    . $token . '&amp;id=' . $id . '&amp;user_id=' . $user_id . '">To publish this testimony, click here.</a></p>'
                    . '<p>If you clicked in error, please contact your web developer.</p>';


        // send email
        if(!$mail->send())
        {
            echo 'Mailer error: ' . $mail->ErrorInfo;
            exit();
        }
        else
        {
            $result = true;

            return $result;
        }
    }



    /**
     * Sends thank you email to testimonial author and website owner/designee
     *
     * @param  string $user_email     The user's email address
     * @param  string $user_full_name The user's full name (first & last name)
     *
     * @return boolean
     */
    public static function sendThanksForTestimonialEmail($user_email, $user_full_name)
    {
        //echo "Connected to sendThanksForTestimonialEmail method in Mail class";

        // create new instance of PHPMailer object
        $mail = new \PHPMailer();

        $mail->isSMTP();
        $mail->Host       =  Config::SMTP_SEND_MAIL_INTERNALLY_HOST;
        //$mail->Port       =  Config::SMTP_PORT;
        $mail->SMTPAuth   =  true;
        $mail->Username   =  Config::SMTP_USER;
        $mail->Password   =  Config::SMTP_PASSWORD;
        $mail->SMTPSecure =  'tls';
        $mail->CharSet    =  'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send an email
         */
        // "From" and "To"
        $mail->setFrom('sales@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($user_email, $user_full_name);
        //$mail->addCC('jim.burns@webmediapartners.com');
        //$mail->addAddress('dave.didion@americangymtrader.com', 'Dave');
        $mail->addBCC('sales@americanbiztrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');
        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Thank you';
        $mail->Body = '<h1 style="color:#0000FF;">Thank you!</h1>'
                    . '<p>'
                    . 'Thank you very much ' . $user_full_name
                    . ' for taking the time to post a nice testimonial.'
                    . '</p>'
                    . '<p>'
                    . 'We really appreciate hearing from our clients!'
                    . '</p>'
                    . '<p>'
                    . 'If there&#39;s anything else we can do to better serve '
                    . 'you, please let us know.'
                    . '</p>'
                    . 'Sincerely,'
                    . '<br>'
                    . '<p>'
                    . 'American Biz Trader'
                    . '</p>'
                    . '<p>'
                    . '<a href="http://americanbiztrader.site/testimonials">Click here '
                    . 'to see your testimonial on our website.</a>'
                    . '</p>';


        // send email
        if(!$mail->send())
        {
            echo 'Mailer error: ' . $mail->ErrorInfo;
            exit();
        }
        else {
            $result = true;

            return $result;
        }
    }




    public static function sendTempPassword($user, $tmp_pass)
    {
        // echo "Connected to setNewPassword method in Mail class!";

        // echo '<pre>';
        // print_r($user);
        // echo '</pre>';
        // exit();

        /**
         *  PHPMailer
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // resource: https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps

        //echo get_class($mail);
        $mail->isSMTP();
        $mail->Host     = Config::SMTP_HOST;
        $mail->Port     = Config::SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->CharSet = 'UTF-8';

        /**
         * Enable SMTP debug messages
         */
        //$mail->SMTPDebug = 2;
        //$mail->Debugoutput = 'html';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send an email
         */
        // "From" and "To"
        $mail->setFrom('noreply@americanbiztrader.com', 'ABT'); // gmail
        // overrides this setting with SMTP_USER
        $mail->addAddress($user->email, $user->first_name);

        // multiple "To" addresses
        //$mail->addAddress('sales@webmediapartners.com', 'Jim');
        //$mail->addAddress('info@webmediapartners.com');

        // "Cc" addresses
        //$mail->addCC('jim.burns@webmediapartners.com', 'Jim');
        //$mail->addCC('jim.burns14@gmail.com');

        // "Bcc" address
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');
        //$mail->addBCC('jim.burns@webmediapartners.com');

        // add different "Reply to" email address
        //$mail->addReplyTo('danat927@gmail.com', 'Dana');

        // sends email as HTML
        $mail->isHTML(true);

        // include plain text version (SPAM filters look for it)

        // add attachment (__FILE__ magic constant that returns full path & file name
        // of the script file); dirname() returns the path w/o file name
        //$mail->addAttachment(dirname(__FILE__) . '/assets/images/koala.jpg', 'newFileName.jpg');
        // add another attachment
        //$mail->addAttachment(dirname(__FILE__) . '/assets/images/penguins.jpg', 'anotherName.jpg');

        // Subject & body
        $mail->Subject = 'Set new password';
        $mail->Body = '<h1 style="color:#0000FF;">Set new password</h1>'
                    . '<h3>American Biz Trader</h3>'
                    . '<p>Temporary password: ' .$tmp_pass. '</p>'
                    . '<p>Please log in with this temporary password and set a permanent password in &quot;My Account&quot;</p>'
                    . '<p><a href="http://americanbiztrader.site/login/temp-pass-login">Click here to login with temporary password</a></p>'
                    . '<p>If you did not request this change, please delete this email. </p>';

        // embed image in email
        //$mail->AddEmbeddedImage(dirname(__FILE__) . '/assets/images/koala.jpg', 'koala');

        // alternative body (plain-text email)
        //$mail->AltBody = "Hello. \nThis is the body in plain text for non-HTML
        //mail clients"; // must use "" or \n will not work

        // send email
        if(!$mail->send())
        {
            echo 'Mailer error: ' . $mail->ErrorInfo;
            exit();
        }
        else {
            $result = true;

            return $result;
        }
    }




    /**
     * Mails contact form data to broker & copy to agents
     *
     * @param  array $listing_inquiry Data for email body
     * @return string                 Boolean
     */
    public static function mailBrokerContactFormData($listing_inquiry)
    {
        // test
        // echo "Connected to mailBrokerContactFormData method in Mail Controller!<br><br>";
        // echo '<pre>';
        // print_r($listing_inquiry);
        // echo '</pre>';
        // exit();

        // change value if user inquiry is for real estate listing
        if(!isset($listing_inquiry['business_name']))
        {
          $listing_inquiry['business_name'] = 'Real estate for ' . $listing_inquiry['type'];
        }

        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('noreply@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($listing_inquiry['broker_email']);
        $mail->addCC($listing_inquiry['agent_email']);
        //$mail->addAddress('jim.burns14@gmail.com');
        //$mail->addCC('dave.didion@americangymtrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        //$mail->addBCC('dave.didion@americangymtrader.com');

        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Prospect from ABT';
        $mail->Body = '<h2 style="color:#0000FF;">Message from American Biz Trader</h2>'
                    . '<p>A prospective buyer is interested in your listing!</p>'
                    . '<h3>Prospect info</h3>'
                    . '<p>Name: ' . $listing_inquiry['first_name'] . ' ' . $listing_inquiry['last_name'] . '</p>'
                    . '<p>Telephone: ' . $listing_inquiry['telephone'] . '</p>'
                    . '<p>Email: ' . $listing_inquiry['email'] . '</p>'
                    . '<p>Investment: $' . number_format($listing_inquiry['investment'], 0) . '</p>'
                    . '<p>Time-frame: ' . $listing_inquiry['time_frame'] . '</p>'
                    . '<p>Message: ' . $listing_inquiry['message'] . '</p>'
                    . '<h3>Listing info</h3>'
                    . '<p>Company: ' . $listing_inquiry['company_name'] . '</p>'
                    . '<p>Listing ID: ' . $listing_inquiry['id'] . '</p>'
                    . '<p>Business name: ' . $listing_inquiry['business_name'] . '</p>'
                    . '<p>Ad title: ' . $listing_inquiry['ad_title'] . '</p>'
                    . '<p>Agent: ' . $listing_inquiry['agent_first_name'] . ' ' . $listing_inquiry['agent_last_name'] . '</p>'
                    . '<h3 style="color:#0000ff;font-style:italic;">Thank you for using AmericanBizTrader.com!</h3>'
                    . '<p>end of message</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }




    /**
     * Mails contact form data to broker & copy to agents
     *
     * @param  array $listing_inquiry Data for email body
     * @return string                 Boolean
     */
    public static function mailBrokerOnlyContactFormData($listing_inquiry)
    {
        // test
        // echo "Connected to mailBrokerOnlyContactFormData method in Mail Controller!<br><br>";
        // echo $broker_id;
        // exit();

        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('noreply@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($listing_inquiry['broker_email']);
        //$mail->addAddress('jim.burns14@gmail.com');
        //$mail->addCC('dave.didion@americangymtrader.com');
        $mail->addBCC('noreply@americanbiztrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');

        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Visitor message from ABT';
        $mail->Body = '<h2 style="color:#0000FF;">Message from American Biz Trader</h2>'
                    . '<h3>To: ' . $listing_inquiry['company_name'].':</h3>'
                    . '<p>A website visitor sent you a message!</p>'
                    . '<h3>Prospect info</h3>'
                    . '<p>Name: ' . $listing_inquiry['first_name'] . ' ' . $listing_inquiry['last_name'] . '</p>'
                    . '<p>Telephone: ' . $listing_inquiry['telephone'] . '</p>'
                    . '<p>Email: ' . $listing_inquiry['email'] . '</p>'
                    . '<p>Message: ' . $listing_inquiry['message'] . '</p>'
                    . '<h3 style="color:#0000ff;font-style:italic;">Thank you for using AmericanBizTrader.com!</h3>'
                    . '<p>end of message</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }




    /**
     * Mails contact form data to agent & copy to broker
     *
     * @param  array $listing_inquiry Data for email body
     * @return string                 Boolean
     */
    public static function mailAgentOnlyContactFormData($listing_inquiry)
    {
        // test
        // echo "Connected to mailBrokerOnlyContactFormData method in Mail Controller!<br><br>";
        // echo $broker_id;
        // exit();

        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('noreplyh@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($listing_inquiry['agent_email']);
        $mail->addCC($listing_inquiry['broker_email']);
        //$mail->addAddress('jim.burns14@gmail.com');
        //$mail->addCC('dave.didion@americangymtrader.com');
        $mail->addBCC('sales@americanbiztrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');

        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Visitor message from ABT';
        $mail->Body = '<h2 style="color:#0000FF;">Message from American Biz Trader</h2>'
                    . '<h3>To: ' . $listing_inquiry['company_name'] . '</h3>'
                    . '<h3>For: ' . $listing_inquiry['agent_first_name'] . ' ' . $listing_inquiry['agent_last_name'] . '</h3>'
                    . '<p>A website visitor sent you a message!</p>'
                    . '<h3>Prospect info</h3>'
                    . '<p>Name: ' . $listing_inquiry['first_name'] . ' ' . $listing_inquiry['last_name'] . '</p>'
                    . '<p>Telephone: ' . $listing_inquiry['telephone'] . '</p>'
                    . '<p>Email: ' . $listing_inquiry['email'] . '</p>'
                    . '<p>Message: ' . $listing_inquiry['message'] . '</p>'
                    . '<h3 style="color:#0000ff;font-style:italic;">Thank you for using AmericanBizTrader.com!</h3>'
                    . '<p>end of message</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }


    /**
     * Sends login notification email to `brokers`.`broker_email`
     *
     * @param  Object   $broker   The broker
     * @param  Object   $user     The user
     *
     * @return boolean
     */
    public static function loginNotification($broker, $user)
    {
        // test
        // echo "Connected to loginNotification() method in Mail Controller!<br><br>";
        // echo '<pre>';
        // echo $broker;
        // echo '</pre>';
        // exit();

        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('noreplyh@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($broker->broker_email);
        //$mail->addAddress('jim.burns14@gmail.com');
        //$mail->addCC('dave.didion@americangymtrader.com');
        $mail->addBCC('sales@americanbiztrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');

        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Log In';
        $mail->Body = '<h2 style="color:#0000FF;">Message from American Biz Trader</h2>'
                    . '<h3>To: ' . $broker->first_name . ' ' . $broker->last_name . ', ' . $broker->company_name . '</h3>'
                    . '<h3>Log In Notification</h3>'
                    . '<p>Authorized user <strong><q>' . $user->first_name . ' ' . $user->last_name . '</q></strong> just logged in.</p>'
                    . '<h3 style="color:#0000ff;font-style:italic;">Thank you for using AmericanBizTrader.com!</h3>'
                    . '<p>end of message</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }


    /**
     * Sends logout notification email to `brokers`.`broker_email`
     *
     * @param  Object   $broker   The broker
     * @param  Object   $user     The user
     *
     * @return boolean
     */
    public static function logoutNotification($broker, $user)
    {
        // test
        // echo "Connected to logoutNotification() method in Mail Controller!<br><br>";
        // echo '<pre>';
        // echo $broker;
        // echo '</pre>';
        // exit();

        /**
         * create instance of PHPMailer object
         */
        $mail = new \PHPMailer(); // backslash required if class in root namespace

        // settings
        $mail->isSMTP();
        $mail->Host = Config::SMTP_HOST;
        $mail->Port = Config::SMTP_PORT; // not requied for server to server mail
        $mail->SMTPAuth = true;
        $mail->Username = Config::SMTP_USER;
        $mail->Password = Config::SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls'; // not required for server to server mail
        $mail->CharSet = 'UTF-8';

        /**
         * solution
         * @https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
         */
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        /**
         * Send email
         */
        $mail->setFrom('noreplyh@americanbiztrader.com', 'AmericanBizTrader');
        $mail->addAddress($broker->broker_email);
        //$mail->addAddress('jim.burns14@gmail.com');
        //$mail->addCC('dave.didion@americangymtrader.com');
        $mail->addBCC('sales@americanbiztrader.com');
        $mail->addBCC('jim.burns14@gmail.com');
        $mail->addBCC('dave.didion@americangymtrader.com');

        $mail->isHTML(true);

        // Subject & body
        $mail->Subject = 'Log Out';
        $mail->Body = '<h2 style="color:#0000FF;">Message from American Biz Trader</h2>'
                    . '<h3>To: ' . $broker->first_name . ' ' . $broker->last_name . ', ' . $broker->company_name . '</h3>'
                    . '<h3>Log Out Notification</h3>'
                    . '<p>Authorized user <strong><q>'. $user->first_name . ' ' . $user->last_name . '</strong></q> just logged out.</p>'
                    . '<h3 style="color:#0000ff;font-style:italic;">Thank you for using AmericanBizTrader.com!</h3>'
                    . '<p>end of message</p>';

        // send mail & return $result to controller
        if($mail->send())
        {
            $result = true;

            return $result;
        }

        // if mail fails display error message
        if(!$mail->send())
        {
           echo $mail->ErrorInfo;
        }
    }

}
