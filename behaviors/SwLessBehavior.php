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
 *					'cacheId' => 'cache', // define cache component to use
 *					'cacheDuration' => 0, // default value infinite duration
 *					'forceRefresh' => false, // default value : do not recompile files
 *					'formatter' => 'lessjs', // default output format
 *					'variables' => array(), // variables to expand
 *					'directory' => 'application.less', // directory where less files are stored
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
 * 			Yii::app()->clientScript->registerLessFile('sweelix.less');
 * 			// or
 * 			Yii::app()->clientScript->registerLess('.block { width : (3px * 2); }');
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
	const CACHE_PATH='application.runtime.less';
	const CACHE_KEY_PREFIX='Sweelix.LessCompilation.';
	/**
	 * Attaches the behavior object only if owner is instance of CClientScript
	 * or one of its derivative
	 * @see CBehavior::attach()
	 *
	 * @param CClientScript $owner the component that this behavior is to be attached to.
	 *
	 * @return void
	 * @since  XXX
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
	 * get current cache id
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getCacheId() {
		return $this->_cacheId;
	}

	/**
	 * @var array less snippets registered
	 */
	protected $less;

	/**
	 * @var array less files registered
	 */
	protected $lessFiles;

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

		if($this->isLessFileRegistered($url) === false) {
			if(($this->getForceRefresh() === true) || (is_file($cssFilePath) === false) || (filemtime($lessFilePath) >= filemtime($cssFilePath))) {
				$this->compileFile($url, $cssFilePath);
			}
			$this->lessFiles[$url]=$media;
		}
		$urlCss = Yii::app()->getAssetManager()->publish($cssFilePath, false, 0, $this->getForceRefresh());

		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerLessFile',$params);

		Yii::endProfile('SwLessBehavior.registerLessFile','sweekit.profile');
		return $this->getOwner()->registerCssFile($urlCss, $media);
	}

	/**
	 * Register less css code
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
		$this->less[$id]=array($less,$media);

		$params=func_get_args();
		$this->recordCachingAction('clientScript','registerLess',$params);

		Yii::endProfile('SwLessBehavior.registerLess','sweekit.profile');
		return $this->getOwner()->registerCss($id.'-less', $css, $media);
	}

	/**
	 * Check if snippet is registered
	 *
	 * @param string $id snippet id
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function isLessRegistered($id) {
		return isset($this->less[$id]);
	}

	/**
	 * Check if file is registered
	 *
	 * @param string $url file url
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function isLessFileRegistered($url) {
		return isset($this->lessFiles[$url]);
	}

	private $_formatter;
	/**
	 * Get current formatter
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getFormatter() {
		return $this->_formatter;
	}

	/**
	 * Define the formatter to use. Can be
	 * compressed, classic or lessjs (default)
	 *
	 * @param string $formatter
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setFormatter($formatter) {
		if(in_array($formatter, array('lessjs', 'compressed', 'classic')) === true) {
			$this->_formatter = $formatter;
			if($this->_compiler !== null) {
				$this->_compiler->setFormatter($formatter);
			}
		}
	}

	private $_variables;

	/**
	 * Get dynamic less variable to use
	 *
	 * @return array
	 * @since  XXX
	 */
	public function getVariables() {
		return $this->_variables;
	}

	/**
	 * Define variables to expand in parsed less files
	 *
	 * @param array $variables variables to expand
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setVariables($variables) {
		$this->_variables = $variables;
		if($this->_compiler !== null) {
			$this->_compiler->setVariables($variables);
		}
	}

	private $_lessDirectory;

	/**
	 * Define the directory where less files are published.
	 * The directory must be defined usin a pathalias
	 *
	 * @param string $directory path alias to the less directory
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setDirectory($directory) {
		$this->_lessDirectory = Yii::getPathOfAlias($directory);
		if($this->_compiler !== null) {
			$this->_compiler->setImportDir($this->_lessDirectory);
		}
	}

	/**
	 * Retrieve real less path
	 *
	 * @return string
	 * @since  XXX
	 */
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

	/**
	 * Get cache directory. Default to protected.runtime.less
	 * This directory is used to pre-publish css files
	 *
	 * @return string
	 * @since  XXX
	 */
	public function getCacheDirectory() {
		if($this->_cacheDirectory === null) {
			$this->_cacheDirectory = Yii::getPathOfAlias(self::CACHE_PATH);
			if(is_dir($this->_cacheDirectory) === false) {
				mkdir($this->_cacheDirectory, 0777, true);
			}
		}
		return $this->_cacheDirectory;
	}

	/**
	 * Wraps the original compile function @see lessc::compile for detailed
	 * information
	 *
	 * @param string $less less code to compile
	 *
	 * @return string
	 * @since  XXX
	 */
	public function compile($less) {
		return $this->getCompiler()->compile($less);
	}

	/**
	 * Wraps the original compileFile function @see lessc::compileFile for detailed
	 * information
	 *
	 * @param string $lessFile original less file to compile
	 * @param string $cssFile  compiled css file
	 *
	 * @return mixed
	 * @since  XXX
	 */
	public function compileFile($lessFile, $cssFile=null) {
		$result = false;
		$lessFile = $this->getDirectory().DIRECTORY_SEPARATOR.$lessFile;
		if(is_file($lessFile) === true) {
			$result = $this->getCompiler()->compileFile($lessFile, $cssFile);
		}
		return $result;
	}

	private $_compiler;

	/**
	 * Lazy load the less compiler
	 *
	 * @return lessc
	 * @since  XXX
	 */
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
			if($this->getDirectory() !== null) {
				$this->_compiler->setImportDir($this->getDirectory());
			}
		}
		return $this->_compiler;
	}

	private $_cacheDuration = 0;

	/**
	 * Define cache duration for less code blocks
	 *
	 * @param integer $cacheDuration @see CCache::get for more information
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setCacheDuration($cacheDuration) {
		$this->_cacheDuration;
	}

	/**
	 * Get cache duration to use
	 *
	 * @return integer
	 * @since  XXX
	 */
	public function getCacheDuration() {
		return $this->_cacheDuration;
	}


	/**
	 * Records a method call when an output cache is in effect.
	 * This is a shortcut to Yii::app()->controller->recordCachingAction.
	 * In case when controller is absent, nothing is recorded.
	 * @param string $context a property name of the controller. It refers to an object
	 * whose method is being called. If empty it means the controller itself.
	 * @param string $method the method name
	 * @param array $params parameters passed to the method
	 * @see COutputCache
	 */
	protected function recordCachingAction($context,$method,$params) {
		if(($controller=Yii::app()->getController())!==null)
			$controller->recordCachingAction($context,$method,$params);
	}
}
