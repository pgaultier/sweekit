<?php
/**
 * File SwRenderBehavior.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   Sweeml.behaviors
 * @since     1.1
 */

/**
 * Class SwRenderBehavior
 * 
 * This behavior implements two methods in the
 * controller which will be used heavily @see Sweeml
 * 
 * <code>
 * 	...
 * 	class MyController extends CController {
 * 		public function behaviors() {
 * 			return array(
 * 				'sweelixRendering' => array(
 * 					'class' => 'ext.sweekit.behaviors.SwRenderBehavior',
 * 				),
 * 			);
 * 		}
 * 	}
 * 	... 
 * </code>
 * 
 * With this behavior active, we can now perform : 
 * <code>
 * 	...
 * 	class MyController extends CController {
 * 		...
 * 		public function actionTest() {
 * 			if(Yii::app()->request->isAjaxRequest == true) {
 * 				// this will raise an event using sweelix callback in order to open a shadowbox
 * 				$this->renderJs(Sweeml::raiseOpenShadowbox(array('index'), array('width'=>400, 'height'=>250));
 * 			}
 * 			...
 * 		}
 * 		...
 * 	}
 * 	... 
 * </code>
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   Sweeml.behaviors
 * @since     1.1
 */
class SwRenderBehavior extends CBehavior {

	/**
	 * Attaches the behavior object only if owner is instance of CController
	 * or one of its derivative
	 * @see CBehavior::attach()
	 * 
	 * @param CController $owner the component that this behavior is to be attached to.
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function attach($owner) {
		if($owner instanceof CController) {
			parent::attach($owner);
		} else {
			throw new CException(__CLASS__.' can only be attached ot a CController instance');
		}
	}
	
	/**
	 * Redirects the browser to the specified URL or route (controller/action).
	 * 
	 * @param mixed   $url       the URL to be redirected to. If the parameter is an array,
	 *                           the first element must be a route to a controller action and the rest
	 *                           are GET parameters in name-value pairs.
	 * @param integer $timer     time in seconds to wait before executing redirect
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function redirectJs($url, $timer=null, $terminate=true) {
		$redirectJs = Sweeml::raiseRedirect($url, $timer);
		if(Yii::app()->getRequest()->getIsAjaxRequest() === true) {
			header('Content-Type: application/javascript');
			echo $redirectJs;
		} else {
			Yii::app()->getClientScript()->registerScript('redirect', $redirectJs, CClientScript::POS_READY);
			$this->getOwner()->renderText(' ');
		}
		if($terminate === true) {		
			Yii::app()->end();
		}
	}	
	/**
	 * Render pure javascript to ease ajax communication
	 * 
	 * @param string  $script    javascript to send back to the client
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function renderJs($script, $terminate=true) {
		header('Content-Type: application/javascript');
		echo $script;
		if($terminate === true) {
			Yii::app()->end();
		}
	}
	/**
	 * Render pure javascript to ease ajax communication
	 * 
	 * @param array   $data      php object to send as json
	 * @param boolean $terminate whether to terminate the current application after calling this method
	 * 
	 * @return void
	 * @since  1.1.0
	 */
	public function renderJson($data, $httpCode=200, $terminate=true) {
		header('Content-Type: application/json');
		self::sendHeader($httpCode);
		echo CJavaScript::jsonEncode($data);
		if($terminate === true) {
			Yii::app()->end();
		}
	}
	
	/**
	 * compute correct header base on http code
	 * 
	 * @param integer $status http code
	 * 
	 * @return void
	 */
	protected static function sendHeader($status) {  
		$codes = array(  
			100 => 'Continue',  
			101 => 'Switching Protocols',  
			200 => 'OK',  
			201 => 'Created',  
			202 => 'Accepted',  
			203 => 'Non-Authoritative Information',  
			204 => 'No Content',  
			205 => 'Reset Content',  
			206 => 'Partial Content',  
			300 => 'Multiple Choices',  
			301 => 'Moved Permanently',  
			302 => 'Found',  
			303 => 'See Other',  
			304 => 'Not Modified',  
			305 => 'Use Proxy',  
			306 => '(Unused)',  
			307 => 'Temporary Redirect',  
			400 => 'Bad Request',  
			401 => 'Unauthorized',  
			402 => 'Payment Required',  
			403 => 'Forbidden',  
			404 => 'Not Found',  
			405 => 'Method Not Allowed',  
			406 => 'Not Acceptable',  
			407 => 'Proxy Authentication Required',  
			408 => 'Request Timeout',  
			409 => 'Conflict',  
			410 => 'Gone',  
			411 => 'Length Required',  
			412 => 'Precondition Failed',  
			413 => 'Request Entity Too Large',  
			414 => 'Request-URI Too Long',  
			415 => 'Unsupported Media Type',  
			416 => 'Requested Range Not Satisfiable',  
			417 => 'Expectation Failed',  
			500 => 'Internal Server Error',  
			501 => 'Not Implemented',  
			502 => 'Bad Gateway',  
			503 => 'Service Unavailable',  
			504 => 'Gateway Timeout',  
			505 => 'HTTP Version Not Supported'  
		);
		if(isset($codes[$status]) === true) {
			header("HTTP/1.0 ".$status." ".$codes[$status]);
		}  
	}
}
