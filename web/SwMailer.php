<?php
/**
 * File SwMailer.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 */

/**
 * Class SwMailer
 *
 * This class allow easy email manipulation. The class is abstract and
 * must be extended.
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  web
 * @package   sweekit.web
 * @since     XXX
 */
abstract class SwMailer extends CComponent {

	/**
	 * Retrieve mailer instance
	 *
	 * @return SwMailer
	 * @since  XXX
	 */
	abstract public static function getInstance();

	/**
	 * Send email to multiple users
	 *
	 * @param string $campaign name used to filter emails
	 * @param array  $users    users must be an array of array : array(array('email' => 'user@email.com', 'name' => 'User name'), ...)
	 *
	 * @return boolean;
	 * @since  XXX
	 */
	abstract public function sendCampaign($campaign, $users);

	/**
	 * Send an email to one user
	 *
	 * @param string $campaign name used to filter emails
	 * @param string $email    target user email
	 * @param string $name     target user name
	 *
	 * @return boolean
	 * @since  XXX
	 */
	abstract public function send($campaign, $email, $name=null);

	/**
	 * Define content to send
	 *
	 * @param string $subject     email subject
	 * @param string $htmlBody    html used to populate the email
	 * @param string $textualBody text used for the email
	 *
	 * @return void
	 * @since  XXX
	 */
	abstract public function setContent($subject, $htmlBody=null, $textualBody=null);

	/**
	 * Define the replyTo field
	 *
	 * @param string $email email for reply to
	 *
	 * @return void
	 * @since  XXX
	 */
	abstract public function setReplyTo($email);

	/**
	 * Retrieve current replyTo setting
	 *
	 * @return string
	 * @since  XXX
	 */
	abstract public function getReplyTo();

	/**
	 * Define the from field
	 *
	 * @param string $email email for reply to
	 * @param string $name  readable name
	 *
	 * @return void
	 * @since  XXX
	 */
	abstract public function setFrom($email, $name=null);

	/**
	 * Retrieve current from settings array('email' => $email, 'name' => $name)
	 *
	 * @return array
	 * @since  XXX
	 */
	abstract public function getFrom();

}