<?php
/**
 * SwMobileNotififier.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mobile
 * @package   Sweeml.mobile
 */

Yii::import('ext.sweekit.mobile.SwMobileNotifierInterface');

/**
 * This SwMobileNotififier is an application component
 * which allow users to send notification to mobile devices.
 * Currently supported systems are
 *  * apns (iOS)
 *  * c2dm (Android)
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   2.0.0
 * @link      http://www.sweelix.net
 * @category  mobile
 * @package   Sweeml.mobile
 */
class SwMobileNotifier extends CApplicationComponent implements SwMobileNotifierInterface {
	/**
	 * @var string mode : production or devel
	 */
	public $mode;

	/**
	 * @var boolean set to true if we have to embed the cafile (usefull when ssl dir is outdated)
	 */
	public $embeddedCaFile=false;

	public $notifiers=array();

	protected $_notifiers;

	/**
	 * Initializes the application component.
	 * This method is required by {@link IApplicationComponent} and is invoked by application.
	 *
	 * @return void
	 * @since  1.9.0
	 */
	public function init() {
		parent::init();
		foreach($this->notifiers as $key => $notifier) {
			$this->_notifiers[] = Yii::createComponent($notifier);
		}
	}

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
	 * @since  2.0.0
	 */
	public function prepare($deviceId, $payload, $parameters=null) {
		for($i=0; $i< count($this->_notifiers); $i++) {
			$this->_notifiers[$i]->prepare($deviceId, $payload, $parameters);
		}
	}

	/**
	 * Send the notifications
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function notify() {
		for($i=0; $i< count($this->_notifiers); $i++) {
			$this->_notifiers[$i]->notify();
		}
	}

	/**
	 * Get status for current queue.
	 *
	 * @see SwMobileNotifierInterface::getStatus()
	 *
	 * @return array
	 * @since  2.0.0
	 */
	public function getStatus() {
		$status = array();
		for($i=0; $i< count($this->_notifiers); $i++) {
			$status = array_merge($status, $this->_notifiers[$i]->getStatus());
		}
		return $status;
	}

}
