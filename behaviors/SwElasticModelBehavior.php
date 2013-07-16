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

 	private $_templateConfigParameters = array();
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
 		$hasProp = ((in_array($property, $this->_elasticAttributeNames) === true) && (isset($this->_elasticAttributes[$property]) === true) && (empty($this->_elasticAttributes[$property]) === false));
		return $hasProp;
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
		if(property_exists($this,$name) === true) {
			$this->$name=$value;
		} elseif(in_array($name, $this->_elasticAttributeNames) === true) {
			$this->_elasticAttributes[$name]=$value;

		} else
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

	public function getNodeId() {
		return $this->getOwner()->nodeId;
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
  			$attributesBehaviors = array();
 			foreach($this->getTemplateConfig() as $attribute => $config) {
 				// patch for testing

 				$modelCfg = $config['model'];
 				if(is_array($modelCfg) === true) {

 					$this->_elasticAttributeNames[] = $attribute;
 					if((isset($modelCfg['rules']) === true) && (is_array($modelCfg['rules']) === true)) {
 						foreach($modelCfg['rules'] as $rule) {
 							array_unshift($rule, $attribute);
 							$this->_elasticRules[] = $rule;
 						}
 					}
 					if(isset($modelCfg['label']) === true) {
 						$this->_elasticLabels[$attribute] = $modelCfg['label'];
 					} else {
 						$this->_elasticLabels[$attribute] = ucwords(trim(strtolower(str_replace(array('-','_','.'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $attribute)))));
 					}
 					if(isset($modelCfg['value']) === true) {
 						$this->_elasticAttributes[$attribute] = $modelCfg['value'];
 					} else {
 						$this->_elasticAttributes[$attribute] = null;
 					}
 				}

 				$elementCfg = $config['element'];
 				if (is_array($elementCfg) === true) {
 					if (isset($elementCfg['type']) === true && $elementCfg['type'] === 'asyncfile') {
 						$attributesBehaviors[$attribute] = array(
 							'asString' => false,
 							'isMulti' => isset($elementCfg['config']['multiSelection']) ? CPropertyValue::ensureBoolean($elementCfg['config']['multiSelection']) : false,
 							'targetPathAlias' => isset($modelCfg['targetPathAlias']) ? $modelCfg['targetPathAlias'] : null,
 							'targetUrl' => isset($modelCfg['targetUrl']) ? $modelCfg['targetUrl'] : null,
 						);
 					}
 				}

 			}
 			if (count($attributesBehaviors) > 0) {
 				$this->attachBehavior('fileUploader', array(
 					'class' => 'ext.sweekit.behaviors.SwUploadedFileBehavior',
 					'ownerModel' => $this->getOwner(),
 					'pathParameters' => $this->getPathParameters(),
 					'attributesForFile' => $attributesBehaviors,
 				));
 			}
 			$this->_configured = true;
 		}
 	}

 	/**
 	 * This function reload the template.
 	 * It is used when the assignement of the templateId is done after the creation.
 	 * Ex :
 	 * $node = new SwNode();
 	 * $node->attributes = $_POST['SwNode'];
 	 * Here we need to reconfigure the node to reload the possible templateId assigned in the $_POST.
 	 */
 	public function reconfigure() {
 		$this->_template = null;
 		$this->_configured = false;
 		$this->_elasticAttributeNames = array();
 		$this->_elasticRules = array();
 		$this->_elasticAttributes = array();
 		$this->_elasticLabels = array();
 		$this->configure();
 	}

 	private $_pathParameters=array();
 	/**
 	 * Define path parameters
 	 *
 	 * @param array $pathParameters path parameters to expand
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function setPathParameters($pathParameters) {
 		$this->_pathParameters = $pathParameters;
 	}
 	/**
 	 * Get path parameters to expand
 	 *
 	 * @return array
 	 */
 	public function getPathParameters() {
 		return $this->_pathParameters;
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
		$this->beforeSave();
 		$values = CJSON::encode($this->_elasticAttributes);
		$this->getOwner()->{$this->getElasticStorage()} = $values;
 	}

 	/**
 	 * Load elastci attributes afterFind
 	 *
 	 * @param CEvent $event event to pass
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function loadElasticAttributesWithEvent($event) {
 		$this->loadElasticAttributes();
		$this->afterFind();
 	}

 	/**
 	 * Load elastic properties from owner model
 	 *
 	 * @param CEvent $event event
 	 *
 	 * @return void
 	 * @since  XXX
 	 */
 	public function loadElasticAttributes() {
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
		$this->beforeValidate();
 	}


 	/**
 	 * Remove attributes which are not handled by elastic model from the search
 	 * (non-PHPdoc)
 	 * @see CModel::getAttributes()
 	 *
 	 * @return array
 	 * @since  XXX
 	 */
 	public function getAttributes($names=null) {
 		if(is_array($names) === true) {
 			$names = array_intersect($this->_elasticAttributeNames, $names);
 		}
 		return parent::getAttributes($names);
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
 			'onAfterFind' => 'loadElasticAttributesWithEvent',
 			'onBeforeValidate' => 'validateElasticAttributes',
 			'onAfterDelete' => 'raiseAfterDelete',
 			'onAfterSave' => 'raiseAfterSave',
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

 	/**
 	 * This event is raised before the record is saved.
 	 * By setting {@link CModelEvent::isValid} to be false, the normal {@link save()} process will be stopped.
 	 * @param CModelEvent $event the event parameter
 	 */
 	public function onBeforeSave($event)
 	{
 		$this->raiseEvent('onBeforeSave',$event);
 	}

 	/**
 	 * This event is raised after the record is saved.
 	 * @param CEvent $event the event parameter
 	 */
 	public function onAfterSave($event)
 	{
 		$this->raiseEvent('onAfterSave',$event);
 	}

 	/**
 	 * This event is raised after the record is deleted.
 	 * @param CEvent $event the event parameter
 	 */
 	public function onAfterDelete($event)
 	{
 		$this->raiseEvent('onAfterDelete',$event);
 	}


 	/**
 	 * This event is raised after the record is instantiated by a find method.
 	 * @param CEvent $event the event parameter
 	 */
 	public function onAfterFind($event)
 	{
 		$this->raiseEvent('onAfterFind',$event);
 	}

 	/**
 	 * This method is invoked before saving a record (after validation, if any).
 	 * The default implementation raises the {@link onBeforeSave} event.
 	 * You may override this method to do any preparation work for record saving.
 	 * Use {@link isNewRecord} to determine whether the saving is
 	 * for inserting or updating record.
 	 * Make sure you call the parent implementation so that the event is raised properly.
 	 * @return boolean whether the saving should be executed. Defaults to true.
 	 */
 	protected function beforeSave()
 	{
 		$event = new CEvent($this);
 		if($this->hasEventHandler('onBeforeSave'))
 			$this->onBeforeSave($event);
 		return true;
 	}

 	/**
 	 * This method is invoked before validation starts.
 	 * The default implementation calls {@link onBeforeValidate} to raise an event.
 	 * You may override this method to do preliminary checks before validation.
 	 * Make sure the parent implementation is invoked so that the event can be raised.
 	 * @return boolean whether validation should be executed. Defaults to true.
 	 * If false is returned, the validation will stop and the model is considered invalid.
 	 */
 	protected function beforeValidate()
 	{
 		$event=new CModelEvent($this);
 		$this->onBeforeValidate($event);
 		return $event->isValid;
 	}


 	/**
 	 * This method is invoked after saving a record successfully.
 	 * The default implementation raises the {@link onAfterSave} event.
 	 * You may override this method to do postprocessing after record saving.
 	 * Make sure you call the parent implementation so that the event is raised properly.
 	 */
 	protected function afterSave()
 	{
 		if($this->hasEventHandler('onAfterSave'))
 			$this->onAfterSave(new CEvent($this));
 	}

 	/**
 	 * RebroadCast After save.
 	 */
 	public function raiseAfterSave() {
 		$this->afterSave();
 	}

 	/**
 	 * This method is invoked after deleting a record.
 	 * The default implementation raises the {@link onAfterDelete} event.
 	 * You may override this method to do postprocessing after the record is deleted.
 	 * Make sure you call the parent implementation so that the event is raised properly.
 	 */
 	protected function afterDelete()
 	{
 		if($this->hasEventHandler('onAfterDelete'))
 			$this->onAfterDelete(new CEvent($this));
 	}

 	public function raiseAfterDelete() {
 		$this->afterDelete();
 	}

 	/**
 	 * This method is invoked after each record is instantiated by a find method.
 	 * The default implementation raises the {@link onAfterFind} event.
 	 * You may override this method to do postprocessing after each newly found record is instantiated.
 	 * Make sure you call the parent implementation so that the event is raised properly.
 	 */
 	protected function afterFind()
 	{
 		$event = new CEvent($this);
 		if($this->hasEventHandler('onAfterFind'))
 			$this->onAfterFind($event);
 	}

 }
