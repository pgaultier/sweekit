<?php
/**
 * packages.php
 * 
 * PHP version 5.2+
 * 
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  js
 * @package   Sweeml.js
 */	

return array(
		'sweelix' => array(
			'js' => array('jquery.sweelix.js'),
			'depends' => array('jquery'),
		),
		'debug' => array(
			'js' => array('jquery.sweelix.debug.js'),
			'depends' => array('jquery', 'sweelix'),
		),
		'callback' => array(
			'js' => array('jquery.sweelix.callback.js'),
			'depends' => array('jquery', 'sweelix', 'debug'),
		),
		'ajax' => array(
			'js' => array('jquery.sweelix.ajax.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback'),
		),
		'shadowbox' => array(
			'js' => array('jquery.sweelix.shadowbox.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'shadowboxjs'),
		),
		'shadowboxjs' => array(
			'js' => array('jquery.shadowbox.js'),
			'css' => array('shadowbox.css'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback'),
		),
		'plupload.html5' => array(
			'js' => array('plupload/plupload.html5.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload.html4' => array(
			'js' => array('plupload/plupload.html4.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload.flash' => array(
			'js' => array('plupload/plupload.flash.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload.browserplus' => array(
			'js' => array('plupload/plupload.browserplus.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload.silverlight' => array(
			'js' => array('plupload/plupload.silverlight.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload.gears' => array(
			'js' => array('plupload/plupload.gears.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax', 'plupload'),
		),
		'plupload' => array(
			'js' => array('plupload/plupload.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax'),
		),
		'plupload.full' => array(
			'js' => array('plupload/plupload.full.js'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax'),
		),
		'plupload.ui.plupload' => array(
			'js' => array('plupload/jquery.ui.plupload/jquery.ui.plupload.js'),
			'css' => array('plupload/jquery.ui.plupload/css/jquery.ui.plupload.css'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax'),
		),
		'plupload.ui' => array(
			'js' => array('jquery.sweelix.plupload.js'),
			'css' => array('jquery.sweelix.plupload.css'),
			'depends' => array('jquery', 'sweelix', 'debug', 'callback', 'ajax'),
		),
	);