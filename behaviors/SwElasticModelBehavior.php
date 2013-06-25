<?php
/**
 * SwElasticModelBehavior.php
 *
 * PHP version 5.2+
 *
 * SwExtendedPropertiesBehavior is used handle everything related to extended properties
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweekit.behaviors
 */

/**
 * This class handle prop and extended properties
 *
 * The following getter / setter must be overriden
 *
 * 	/ **
 * 	 * Massive setter
 * 	 *
 * 	 * (non-PHPdoc)
 * 	 * @see CModel::setAttributes()
 * 	 *
 * 	 * @return void
 * 	 * @since  XXX
 * 	 * /
 * 	public function setAttributes($values, $safeOnly=true) {
 * 		if(($this->asa('elasticProperties') !== null) && ($this->asa('elasticProperties')->getEnabled() === true)) {
 * 			$this->asa('elasticProperties')->setAttributes($values, $safeOnly);
 * 			$values = $this->asa('elasticProperties')->filterOutElasticAttributes($values);
 * 		}
 * 		parent::setAttributes($values, $safeOnly);
 * 	}
 *
 * 	/ **
 * 	 * Massive getter
 * 	 *
 * 	 * (non-PHPdoc)
 * 	 * @see CActiveRecord::getAttributes()
 * 	 *
 * 	 * @return array
 * 	 * @since  XXX
 * 	 * /
 * 	public function getAttributes($names=true) {
 * 		$attributes = parent::getAttributes($names);
 * 		if(($this->asa('elasticProperties') !== null) && ($this->asa('elasticProperties')->getEnabled() === true)) {
 * 			$elasticAttributes = $this->asa('elasticProperties')->getAttributes($names);
 * 			$attributes = CMap::mergeArray($attributes, $elasticAttributes);
 * 		}
 * 		return $attributes;
 * 	}
 *
 *
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2013 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   XXX
 * @link      http://www.sweelix.net
 * @category  behaviors
 * @package   sweekit.behaviors
 * @since     XXX
 */
 class SwElasticModelBehavior extends CModel implements IBehavior {

 	/**
 	 * @var string elastic attribute
 	 */
 	private $_elasticStorage;

 	/**
 	 * Define elastic attribute
 	 *
 	 * @param string $attribute elastic storage attribute name
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setElasticStorage($attribute) {
		$this->_elasticStorage = $attribute;
 	}

 	/**
 	 * Get defined elastic attibute
 	 *
 	 * @return string
 	 * @since  XXX
 	 */
 	public function getElasticStorage() {
 		return $this->_elasticStorage;
 	}

 	/**
 	 * Forward scenario from original model
 	 * (non-PHPdoc)
 	 * @see CModel::getScenario()
 	 *
 	 * @return string
 	 * @since  XXX
 	 */
 	public function getScenario() {
 		return $this->getOwner()->getScenario();
 	}

 	/**
 	 * Avoid scenario setting
 	 * (non-PHPdoc)
 	 * @see CModel::setScenario()
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setScenario($value) {
 		throw new CException('Scenario cannot be set directly in elastic model');
 	}

 	/**
 	 * @var array dynamic template data
 	 */
 	private $_template=null;
 	private $_templateConfig = null;

 	/**
 	 * Define current template config
 	 *
 	 * @param array $config elastic model configuration
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setTemplateConfig($config) {
 		if(is_callable($config) === true) {
 			$this->_templateConfig = $config;
 			$this->_template = null;
 		} elseif(is_array($config) === true) {
 			$this->_template = $config;
 		}
 	}

 	private $_templateConfigParameters;
 	public function setTemplateConfigParameters($parameters) {
 		if(is_array($parameters) === true) {
 			$this->_templateConfigParameters = $parameters;
 		}
 	}
 	public function getTemplateConfigParameters() {
 		return $this->_templateConfigParameters;
 	}

 	/**
 	 * Get current template config file
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
 	public function getTemplateConfig() {
		if($this->_template === null) {
			$this->_template = array();
			if($this->_templateConfig !== null) {
				$this->_template = call_user_func_array($this->_templateConfig, $this->_templateConfigParameters);
			}
		}
 		return $this->_template;
 	}

 	/**
 	 * Fetch a property. return null if
 	 * property does not exists.
 	 * If the property has embedded images,
 	 * we replace the images with cached versions
 	 *
 	 * @param string $property  property name to fetch
 	 * @param array  $arrayCell index array
 	 *
 	 * @return mixed
 	 * @since  XXX
 	 */
 	public function prop($property, $arrayCell = null) {
 		$prop = null;
 		if(isset($this->_elasticAttributes[$property]) === true) {
 			if($arrayCell !== null) {
 				$prop = (isset($this->_elasticAttributes[$property][$arrayCell]) === true)?$this->_elasticAttributes[$property][$arrayCell]:null;
 			} else {
 				$prop = $this->_elasticAttributes[$property];
 				$prop = preg_replace_callback('/<img([^>]+)>/', array($this, 'expandImages'), $prop);
 			}
 		}
 		return $prop;
 	}
 	/**
 	 * Perform inline replacement. For each image, if the image
 	 * was defined in the base element, we replace-it with the cached
 	 * version
 	 *
 	 * @param array $matches matches from preg_replace
 	 *
 	 * @return string
 	 * @since  XXX
 	 */
 	protected function expandImages($matches) {
 		$nbMatches = preg_match_all('/([a-z-]+)\="([^"]+)"/', $matches[1], $imgInfos);
 		if ($nbMatches > 0) {
 			$tabParams = array_combine($imgInfos[1], $imgInfos[2]);
 			if (isset($tabParams['data-store'], $tabParams['data-offset']) === true) {
 				if (isset($tabParams['height'], $tabParams['width']) === false) {
 					if(isset($tabParams['style'])) {
 						preg_match_all('/\s*(\w+)\s*:\s*(\w+)\s*;?/', $tabParams['style'], $resData);
 						$newParams = array_combine($resData[1], $resData[2]);
 						if(isset($newParams['width'])) {
 							$tabParams['width'] = str_replace('px', '', $newParams['width']);
 						}
 						if(isset($newParams['height'])) {
 							$tabParams['height'] = str_replace('px', '', $newParams['height']);
 						}
 					}
 				}
 				if (isset($tabParams['height'], $tabParams['width']) === true) {
 					return Sweeml::image(Yii::app()->getRequest()->getBaseUrl().'/'.SwCacheImage::create($this->prop($tabParams['data-store'], $tabParams['data-offset']))->setRatio(false)->resize($tabParams['width'], $tabParams['height'])->getUrl());
 				} else {
 					return Sweeml::image(Yii::app()->getRequest()->getBaseUrl().'/'.SwCacheImage::create($this->prop($tabParams['data-store'], $tabParams['data-offset']))->getUrl());
 				}
 			} else {
 				return $matches[0];
 			}
 		} else {
 			return $matches[0];
 		}
 	}

 	/**
 	 * Check if property has a value
 	 *
 	 * @param string $property  property name to test
 	 *
 	 * @return boolean
 	 * @since  XXX
 	 */
 	public function propHasValue($property) {
 		$props = $this->getOwner()->{$this->baseName};
 		return ((isset($props[$property]) === true) && empty($props[$property]) === false);
 	}

 	/**
 	 * @var array rules for elastic properties
 	 */
 	private $_elasticRules = array();

 	/**
 	 * @var array names of elastic properties
 	 */
 	private $_elasticAttributeNames = array();

 	/**
 	 * @var array labels of elastic properties
 	 */
 	private $_elasticLabels = array();

 	/**
 	 * @var array labels of elastic properties
 	 */
 	private $_elasticAttributes = array();

 	/**
 	 * Defined rules for elastic properties
 	 * (non-PHPdoc)
 	 * @see CModel::rules()
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
	public function rules() {
		return $this->_elasticRules;
	}

	/**
	 * List of the attributes names
	 *
	 * @return array
	 * @since  XXX
	 */
	public function attributeNames() {
		return $this->_elasticAttributeNames;
	}

	/**
	 * Checks whether this elastic model has the named attribute
	 *
	 * @param string $name attribute name
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function hasAttribute($name) {
		return (in_array($name, $this->_elasticAttributeNames) === true);
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name)
	{
		if($this->hasAttribute($name) === true)
			return true;
		else
			return parent::canGetProperty($name);
	}

	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name)
	{
		if($this->hasAttribute($name) === true)
			return true;
		else
			return parent::canSetProperty($name);
	}


	/**
	 * Returns the named attribute value.
	 * @see hasAttribute
	 *
	 * @param string $name the attribute name
	 *
	 * @return mixed
	 * @since  XXX
	 */
	public function getAttribute($name) {
		if(property_exists($this,$name) === true)
			return $this->$name;
		elseif(isset($this->_elasticAttributes[$name]) === true)
			return $this->_elasticAttributes[$name];
	}

	/**
	 * Sets the named attribute value.
	 * return true if everything went well
	 * @see hasAttribute
	 *
	 * @param string $name  the attribute name
	 * @param mixed  $value the attribute value.
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function setAttribute($name,$value) {
		if(property_exists($this,$name) === true)
			$this->$name=$value;
		elseif(in_array($name, $this->_elasticAttributeNames) === true)
			$this->_elasticAttributes[$name]=$value;
		else
			return false;
		return true;
	}

	/**
	 * Massive assignement of elastic attributes
	 *
	 * (non-PHPdoc)
	 * @see CModel::setAttributes()
	 *
	 * @return void
	 * @since  XXX
	 */
	public function setAttributes($values, $safeOnly=true) {
		$filteredValues = array();
		foreach($this->attributeNames() as $name) {
			if(isset($values[$name]) === true) {
				$filteredValues[$name] = $values[$name];
			}
		}
		parent::setAttributes($filteredValues, $safeOnly);
	}

	/**
	 * Remove elastic attributes from the array, usefull to
	 * avoid onUnsafeAttribute
	 *
	 * @param array $values current attributes values
	 *
	 * @return array
	 * @since  XXX
	 */
	public function filterOutElasticAttributes($values) {
		$filteredValues = array();
		foreach($values as $name => $value) {
			if(in_array($name, $this->attributeNames()) === false) {
				$filteredValues[$name] = $value;
			}
		}
		return $filteredValues;
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that elastic attributes can be accessed like properties.
	 * @see getAttribute
	 *
	 * @param string $name property name
	 *
	 * @return mixed
	 */
	public function __get($name) {
		if( in_array($name, $this->_elasticAttributeNames) === true)
			return isset($this->_elasticAttributes[$name])?$this->_elasticAttributes[$name]:null;
		else
			return parent::__get($name);
	}

	/**
	 * PHP setter magic method.
	 * This method is overridden so that elastic attributes can be accessed like properties.
	 *
	 * @param string $name  property name
	 * @param mixed  $value property value
	 *
	 * @return void
	 * @since  XXX
	 */
	public function __set($name,$value) {
		if($this->setAttribute($name,$value)===false) {
			parent::__set($name,$value);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named attribute is null or not.
	 *
	 * @param string $name the property name or the event name
	 *
	 * @return boolean
	 * @since  XXX
	 */
	public function __isset($name) {
		if(isset($this->_elasticAttributes[$name]) === true)
			return true;
		elseif(in_array($name, $this->_elasticAttributeNames) === true)
			return false;
		else
			return parent::__isset($name);
	}

	/**
	 * Sets a component property to be null.
	 * This method overrides the parent implementation by clearing
	 * the specified attribute value.
	 *
	 * @param string $name the property name or the event name
	 *
	 * @return void
	 */
	public function __unset($name) {
		if(in_array($name, $this->_elasticAttributeNames))
			unset($this->_elasticAttributes[$name]);
		else
			parent::__unset($name);
	}

	private $_configured=false;
 	/**
 	 * 'plop' => array(
 	 * 	'rules' => array(),
 	 * 	'label' => 'label',
 	 * 	'type' => 'xxx',
 	 * 	'htmlOptions' => array(),
 	 * )
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function configure() {
 		if($this->_configured === false) {
 			foreach($this->getTemplateConfig() as $attribute => $config) {
 				// patch for testing
 				$config = $config['model'];
 				if(is_array($config) === true) {
 					$this->_elasticAttributeNames[] = $attribute;
 					if((isset($config['rules']) === true) && (is_array($config['rules']) === true)) {
 						foreach($config['rules'] as $rule) {
 							array_unshift($rule, $attribute);
 							$this->_elasticRules[] = $rule;
 						}
 					}
 					if(isset($config['label']) === true) {
 						$this->_elasticLabels[$attribute] = $config['label'];
 					} else {
 						$this->_elasticLabels[$attribute] = ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $attribute)))));
 					}
 					if(isset($config['value']) === true) {
 						$this->_elasticAttributes[$attribute] = $config['value'];
 					} else {
 						$this->_elasticAttributes[$attribute] = null;
 					}
 				}
 			}
 			$this->_configured = true;
 		}
 	}

 	/**
 	 * Store elastic properties in owner model
 	 *
 	 * @param CEvent $event event
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function storeElasticAttributes($event) {
		$values = CJSON::encode($this->_elasticAttributes);
		$this->getOwner()->{$this->getElasticStorage()} = $values;
 	}

 	/**
 	 * Load elastic properties from owner model
 	 *
 	 * @param CEvent $event event
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function loadElasticAttributes($event) {
 		$values = CJSON::decode($this->getOwner()->{$this->getElasticStorage()});
 		if(is_array($values) === true) {
	 		foreach($values as $key => $value) {
	 			if($this->hasAttribute($key) === true) {
	 				$this->_elasticAttributes[$key] = $value;
	 			}
	 		}
 		}
 	}

 	/**
 	 * validate elastic properties and send result to
 	 * owner model
 	 *
 	 * @param CEvent $event event
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function validateElasticAttributes($event) {
		$this->validate();
		if($this->hasErrors() === true) {
			$this->getOwner()->addErrors($this->getErrors());
		}
 	}


 	// handle behavior stuff
 	private $_enabled=false;
 	private $_owner;

 	/**
 	 * Declares events and the corresponding event handler methods.
 	 * The events are defined by the {@link owner} component, while the handler
 	 * methods by the behavior class. The handlers will be attached to the corresponding
 	 * events when the behavior is attached to the {@link owner} component; and they
 	 * will be detached from the events when the behavior is detached from the component.
 	 * Make sure you've declared handler method as public.
 	 * @return array events (array keys) and the corresponding event handler methods (array values).
 	 */
 	public function events() {
 		return array(
 			'onBeforeSave' => 'storeElasticAttributes',
 			'onAfterFind' => 'loadElasticAttributes',
 			'onBeforeValidate' => 'validateElasticAttributes',
 		);
 	}

 	/**
 	 * Attaches the behavior object to the component.
 	 * The default implementation will set the {@link owner} property
 	 * and attach event handlers as declared in {@link events}.
 	 * This method will also set {@link enabled} to true.
 	 * Make sure you've declared handler as public and call the parent implementation if you override this method.
 	 * @param CComponent $owner the component that this behavior is to be attached to.
 	 */
 	public function attach($owner) {
 		$this->_enabled=true;
 		$this->_owner=$owner;
 		$this->configure();
 		$this->_attachEventHandlers();
 	}

 	/**
 	 * Detaches the behavior object from the component.
 	 * The default implementation will unset the {@link owner} property
 	 * and detach event handlers declared in {@link events}.
 	 * This method will also set {@link enabled} to false.
 	 * Make sure you call the parent implementation if you override this method.
 	 * @param CComponent $owner the component that this behavior is to be detached from.
 	 */
 	public function detach($owner) {
 		foreach($this->events() as $event=>$handler)
 			$owner->detachEventHandler($event,array($this,$handler));
 		$this->_owner=null;
 		$this->_enabled=false;
 	}

 	/**
 	 * @return CComponent the owner component that this behavior is attached to.
 	 */
 	public function getOwner() {
 		return $this->_owner;
 	}

 	/**
 	 * @return boolean whether this behavior is enabled
 	 */
 	public function getEnabled() {
 		return $this->_enabled;
 	}

 	/**
 	 * @param boolean $value whether this behavior is enabled
 	 */
 	public function setEnabled($value) {
 		$value=(bool)$value;
 		if($this->_enabled!=$value && $this->_owner) {
 			if($value)
 				$this->_attachEventHandlers();
 			else {
 				foreach($this->events() as $event=>$handler)
 					$this->_owner->detachEventHandler($event,array($this,$handler));
 			}
 		}
 		$this->_enabled=$value;
 	}

 	private function _attachEventHandlers() {
 		$class=new ReflectionClass($this);
 		foreach($this->events() as $event=>$handler) {
 			if($class->getMethod($handler)->isPublic())
 				$this->_owner->attachEventHandler($event,array($this,$handler));
 		}
 	}
 }
