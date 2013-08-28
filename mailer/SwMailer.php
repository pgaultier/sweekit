<?php
/**
 * File SwMailer.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 */

/**
 * Class SwMailer is an abstract class which expose minimal
 * methods
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mailer
 * @package   sweekit.mailer
 * @since     2.0.0
 */
abstract class SwMailer extends CComponent {

	/**
	 * Send email to multiple users
	 *
	 * @param string $campaign name used to filter emails
	 * @param array  $users    users must be an array of array : array(array('email' => 'user@email.com', 'name' => 'User name'), ...)
	 *
	 * @return boolean
	 * @since  2.0.0
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
	 * @since  2.0.0
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
	 * @since  2.0.0
	 */
	abstract public function setContent($subject, $htmlBody=null, $textualBody=null);

	/**
	 * Define the replyTo field
	 *
	 * @param string $email email for reply to
	 *
	 * @return void
	 * @since  2.0.0
	 */
	abstract public function setReplyTo($email);

	/**
	 * Retrieve current replyTo setting
	 *
	 * @return string
	 * @since  2.0.0
	 */
	abstract public function getReplyTo();

	/**
	 * Define the from field
	 *
	 * @param string $email email for reply to
	 * @param string $name  readable name
	 *
	 * @return void
	 * @since  2.0.0
	 */
	abstract public function setFrom($email, $name=null);

	/**
	 * Retrieve current from settings array('email' => $email, 'name' => $name)
	 *
	 * @return array
	 * @since  2.0.0
	 */
	abstract public function getFrom();
 }
