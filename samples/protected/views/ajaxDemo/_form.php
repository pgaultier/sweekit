<?php
/**
 * _form.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  views
 * @package   Sweeml.samples.views.ajaxDemo
 * @since     1.9.0
 */
?>
		<?php echo Sweeml::errorSummary($demoForm)?>
		<?php echo Sweeml::activeLabel($demoForm, 'login')?> : 
		<?php echo Sweeml::activeTextField($demoForm, 'login')?> 
		<?php echo Sweeml::htmlButton('Submit', array('type' => 'submit')); ?>