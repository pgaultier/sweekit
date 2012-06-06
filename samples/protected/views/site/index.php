<?php
/**
 * index.php
 *
 * PHP version 5.2+
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  views
 * @package   Sweeml.samples.views.site
 * @since     1.9.0
 */
?><h1>Sweekit Demos</h1>

<p>
	This sample site show the use of sweekit components through several demos.
</p>

<ul>
	<li><?php echo Sweeml::link('Protocol demo', array('protocolDemo/'))?></li>
	<li><?php echo Sweeml::link('Ajax demo', array('ajaxDemo/'))?></li>
	<li><?php echo Sweeml::link('Shadowbox demo', array('shadowboxDemo/'))?></li>
	<li><?php echo Sweeml::link('Plupload demo', array('uploadDemo/'))?></li>
</ul>
