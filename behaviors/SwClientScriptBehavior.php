<?php
/**
 * File SwClientScriptBehavior.php
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
 * Class SwClientScriptBehavior
 *
 * This behavior implement script management for
 * element used in @see Sweeml
 *
 * <code>
 * 	...
 *		'clientScript' => array(
 *			'behaviors' => array(
 *				'sweelixClientScript' => array(
 *					'class' => 'ext.sweekit.behaviors.SwClientScriptBehavior',
 *				),
 *			),
 *		),
 * 	...
 * </code>
 *
 * With this behavior active, we can now perform :
 * <code>
 * 	...
 * 	class MyController extends CController {
 * 		...
 * 		public function actionTest() {
 * 			...
 * 			Yii::app()->clientScript->registerSweelixScript('sweelix');
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
class SwClientScriptBehavior extends CBehavior {
	public $sweelixScript=array();
	public $sweelixPackages=null;
	private $_assetUrl;
	private $_config;
	private $_shadowboxConfig;
	private $_init=false;
	private $_sbInit=false;
	/**
	 * Attaches the behavior object only if owner is instance of CClientScript
	 * or one of its derivative
	 * @see CBehavior::attach()
	 *
	 * @param CClientScript $owner the component that this behavior is to be attached to.
	 *
	 * @return void
	 * @since  1.1.0
	 */
	public function attach($owner) {
		if($owner instanceof CClientScript) {
			parent::attach($owner);
		} else {
			throw new CException(__CLASS__.' can only be attached ot a CClientScript instance');
		}
	}

	/**
	 * Publish assets to allow script and css appending
	 *
	 * @return string
	 * @since  1.1.0
	 */
	public function getSweelixAssetUrl() {
		if($this->_assetUrl === null) {
			$this->_assetUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'source');
		}
		return $this->_assetUrl;
	}

	/**
	 * Register sweelix script
	 *
	 * @param string $name name of the package we want to register
	 *
	 * @return CClientScript
	 * @since  1.1.0
	 */
	public function registerSweelixScript($name) {
		if(isset($this->sweelixScript[$name]))
			return $this->getOwner();
		if($this->sweelixPackages===null)
			$this->sweelixPackages=require(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'packages.php');
		if(isset($this->sweelixPackages[$name]))
			$package=$this->sweelixPackages[$name];
		if(isset($package)) {
			if(!empty($package['depends'])) {
				foreach($package['depends'] as $p) {
					if(array_key_exists($p, $this->sweelixPackages) == true) {
						$this->registerSweelixScript($p);
					} else {
						$this->getOwner()->registerCoreScript($p);
					}
				}
			}
			if(isset($package['js']) == true) {
				foreach($package['js'] as $js) {
					$this->getOwner()->registerScriptFile($this->getSweelixAssetUrl().'/'.$js);
				}
			}
			if(isset($package['css']) == true) {
				foreach($package['css'] as $css) {
					$this->getOwner()->registerCssFile($this->getSweelixAssetUrl().'/'.$css);
				}
			}
			if($name==='shadowbox') {
				$this->_initShadowbox();
			}
			$this->sweelixScript[$name]=$package;
			if($this->_init === false) {
				$this->getOwner()->registerScript('sweelixInit', 'jQuery.sweelix.init('.CJavaScript::encode($this->_config).');', CClientScript::POS_READY);
				$this->_init=true;
			}
		}
		return $this->getOwner();
	}

	/**
	 * Register shadowbox script and init it in the
	 * page
	 *
	 * @return void
	 * @since  1.1.0
	 */
	private function _initShadowbox() {
		if($this->_sbInit === false) {
			$this->getOwner()->registerScript('shadowboxInit', 'Shadowbox.init('.CJavaScript::encode($this->_shadowboxConfig).');', CClientScript::POS_READY);
			$this->_sbInit=true;
		}
	}

	/**
	 * Define configuration parameters for
	 * javascript packages
	 *
	 * @param array $data initial config
	 *
	 * @return void
	 * @since  1.1.0
	 */
	public function setConfig($data=array()) {
		if(isset($data['shadowbox']) == true) {
			$this->_shadowboxConfig = $data['shadowbox'];
			unset($data['shadowbox']);
		}
		if(!isset($this->_shadowboxConfig['skipSetup'])) {
			$this->_shadowboxConfig['skipSetup'] = true;
		}
		$this->_config=$data;
	}
}
