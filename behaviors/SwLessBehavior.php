<?php
/**
 * File SwLessBehavior.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   Sweeml.behaviors
 * @since     XXX
 */

/**
 * Class SwLessBehavior
 *
 * This behavior implement less compilation and css/less management f
 *
 * <code>
 * 	...
 *		'clientScript' => array(
 *			'behaviors' => array(
 *				'lessClientScript' => array(
 *					'class' => 'ext.sweekit.behaviors.SwLessBehavior',
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
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   Sweeml.behaviors
 * @since     XXX
 */
class SwLessBehavior extends CBehavior {
	const COMPILER_PATH='ext.sweekit.vendors.lessphp';
	const CACHE_KEY_PREFIX='Sweelix.LessCompilation.';
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

	private $_cacheId;
	/**
	 * define the cms cache id
	 *
	 * @param string $cacheId id of cms cache
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setCacheId($cacheId) {
		$this->_cacheId = $cacheId;
	}

	/**
	 * get current cms cache id
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getCacheId() {
		return $this->_cacheId;
	}

	private $_cache;
	/**
	 * Get cache component if everything
	 * was set correctly
	 *
	 * @return CCache
	 * @since  XXX
	 */
	public function getCache() {
		if(($this->_cache === null) && ($this->_cacheId !== null)) {
			$this->_cache = Yii::app()->getComponent($this->_cacheId);
		}
		return $this->_cache;
	}
	/**
	 * Register less file
	 *
	 * @param string $url URL of the LESS file
	 * @param string $media media that the generated CSS file should be applied to. If empty, it means all media types.
	 *
	 * @return CClientScript
	 * @since  XXX
	 */
	public function registerLessFile($url,$media='') {
		Yii::beginProfile('SwLessBehavior.registerLessFile','sweekit.profile');

		$cssFilePath = $this->getCacheDirectory().DIRECTORY_SEPARATOR.pathinfo($url, PATHINFO_FILENAME).'.css';
		$lessFilePath = $this->getDirectory().DIRECTORY_SEPARATOR.$url;

		if(($this->getForceRefresh() === true) || (is_file($cssFilePath) === false) || (filemtime($lessFilePath) >= filemtime($cssFilePath))) {
			$this->compileFile($url, $cssFilePath);
		}

		$urlCss = Yii::app()->getAssetManager()->publish($cssFilePath, false, 0, $this->getForceRefresh());
		Yii::endProfile('SwLessBehavior.registerLessFile','sweekit.profile');
		return $this->getOwner()->registerCssFile($urlCss, $media);
	}

	/**
	 * Register less css file
	 *
	 * @param string $id    ID that uniquely identifies this piece of generated CSS code
	 * @param string $less  the LESS code
	 * @param string $media media that the CSS code should be applied to. If empty, it means all media types.
	 *
	 * @return CClientScript
	 * @since  XXX
	 */
	public function registerLess($id, $less, $media='') {
		Yii::beginProfile('SwLessBehavior.registerLess','sweekit.profile');
		$css = false;
		if(($this->getForceRefresh() === false) && ($this->getCache() !== null)) {
			$cacheKey = self::CACHE_KEY_PREFIX.md5($less);
			$css = $this->getCache()->get($cacheKey);
		}
		if($css === false) {
			$css = $this->getCompiler()->compile($less);
			if(($this->getForceRefresh() === false) && ($this->getCache() !== null)) {
				$this->getCache()->set($cacheKey, $css, $this->getCacheDuration());
			}
		}
		Yii::endProfile('SwLessBehavior.registerLess','sweekit.profile');
		return $this->getOwner()->registerCss($id, $css, $media);
	}

	private $_formatter;
	public function getFormatter() {
		return $this->_formatter;
	}

	public function setFormatter($formatter) {
		if(in_array($formatter, array('lessjs', 'compressed', 'classic')) === true) {
			$this->_formatter = $formatter;
			if($this->_compiler !== null) {
				$this->_compiler->setFormatter($formatter);
			}
		}
	}

	private $_variables;
	public function getVariables() {
		return $this->_variables;
	}

	public function setVariables($variables) {
		$this->_variables = $variables;
		if($this->_compiler !== null) {
			$this->_compiler->setVariables($variables);
		}
	}

	private $_preserveComments;
	public function getPreserveComments() {
		return $this->_preserveComments;
	}

	public function setPreserveComments($preserveComments) {
		$this->_preserveComments = $preserveComments;
		if($this->_compiler !== null) {
			$this->_compiler->setPreserveComments($preserveComments);
		}
	}

	private $_lessDirectory;
	public function setDirectory($directory) {
		$this->_lessDirectory = Yii::getPathOfAlias($directory);
		if($this->_compiler !== null) {
			$this->_compiler->setImportDir($this->_lessDirectory);
		}
	}
	public function getDirectory() {
		return $this->_lessDirectory;
	}

	private $_forceRefresh = false;
	public function setForceRefresh($forceRefresh) {
		$this->_forceRefresh = $forceRefresh;
	}
	public function getForceRefresh() {
		return $this->_forceRefresh;
	}

	private $_cacheDirectory;
	public function getCacheDirectory() {
		if($this->_cacheDirectory === null) {
			$this->_cacheDirectory = Yii::getPathOfAlias('application.runtime.less');
			if(is_dir($this->_cacheDirectory) === false) {
				mkdir($this->_cacheDirectory, 0777, true);
			}
		}
		return $this->_cacheDirectory;
	}

	public function compile($less) {
		return $this->getCompiler()->compile($less);
	}

	public function compileFile($lessFile, $cssFile=null) {
		$result = false;
		$lessFile = $this->getDirectory().DIRECTORY_SEPARATOR.$lessFile;
		if(is_file($lessFile) === true) {
			$result = $this->getCompiler()->compileFile($lessFile, $cssFile);
		}
		return $result;
	}

	private $_compiler;
	protected function getCompiler() {
		if($this->_compiler === null) {
			require_once(Yii::getPathOfAlias(self::COMPILER_PATH).DIRECTORY_SEPARATOR.'lessc.inc.php');
			$this->_compiler = new lessc();
			if($this->getFormatter() !== null) {
				$this->_compiler->setFormatter($this->getFormatter());
			}
			if($this->getVariables() !== null) {
				$this->_compiler->setVariables($this->getVariables());
			}
			if($this->getPreserveComments() !== null) {
				$this->_compiler->setPreserveComments($this->getPreserveComments());
			}
			if($this->getDirectory() !== null) {
				$this->_compiler->setImportDir($this->getDirectory());
			}
		}
		return $this->_compiler;
	}

	private $_cacheDuration = 0;
	public function setCacheDuration($cacheDuration) {
		$this->_cacheDuration;
	}
	public function getCacheDuration() {
		return $this->_cacheDuration;
	}
}
