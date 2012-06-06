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
 * @package   Sweeml.samples.views.shadowboxDemo
 * @since     1.9.0
 */
?><h1><?php echo Sweeml::link('Main', array('site/'));?> > Shadowbox tools demo</h1>

<h2>Sweekit components used</h2>

<ul>
	<li>SwClientScriptBehavior : automatic script insertion</li>
	<li>SwRenderBehavior : return data with correct datatype</li>
</ul>

<h2>Opening a shadowbox</h2>

<h3>Using inline content</h3>

<h4>Demo</h4>
By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseOpenShadowboxUrl('#', array('player' => 'html', 'content' => '<div style="background-color:#ffffff">Inline content</div>'))); ?> you will display inline content in the shadowbox

<h4>Code</h4>
<?php echo highlight_string("By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseOpenShadowboxUrl('#', array('player' => 'html', 'content' => '<div style=\"background-color:#ffffff\">Inline content</div>'))); ?> you will display inline content in the shadowbox", true)?>


<h3>Using iframe content</h3>


<h4>Demo</h4>
By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseOpenShadowboxUrl(array('displayInfo'))); ?> you will display inline content in the shadowbox

<h4>Code</h4>
<?php echo highlight_string("By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseOpenShadowboxUrl(array('displayInfo'))); ?> you will display inline content in the shadowbox", true)?>


