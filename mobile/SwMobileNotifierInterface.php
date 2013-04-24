<?php
/**
 * SwMobileNotifierInterface.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  mobile
 * @package   sweekit.mobile
 */

/**
 * All added notifiers must implement this interface.
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  mobile
 * @package   sweekit.mobile
 *
 */
interface SwMobileNotifierInterface {
	/**
	 * Prepare one or more messages
	 * @see SwMobileNotifierInterface::prepare()
	 *
	 *
	 * @param mixed $deviceIds  string if one device is the target else an array with the list of all targets
	 * @param array $payload    an array which contains all the data to send.
	 * @param array $parameters an array of extended parameters
	 *
	 * @return void
	 * @since  XXX
	 */
	public function prepare($deviceId, $payload, $parameters=null);

	/**
	 * Send the notifications
	 *
	 * @return void
	 * @since  XXX
	 */
	public function notify();

	/**
	 * Get status for current queue.
	 *
	 * @see SwMobileNotifierInterface::getStatus()
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getStatus();
}