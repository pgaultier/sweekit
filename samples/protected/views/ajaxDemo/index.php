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
 * @package   Sweeml.samples.views.ajaxDemo
 * @since     1.9.0
 */
?><h1><?php echo Sweeml::link('Main', array('site/'));?> > Ajax tools demo</h1>

<h2>Sweekit components used</h2>

<ul>
	<li>SwClientScriptBehavior : automatic script insertion</li>
	<li>SwRenderBehavior : return data with correct datatype</li>
</ul>

<h2>Refreshing a bloc using ajax Call</h2>

<p>This function is driven by the controller. If the controller sends javascript, it will be executed otherwise
the selected html bloc will be updated / replaced</p>

<h3>Classic HTML replacement / update</h3>

<h4>Demo</h4>
By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseAjaxRefreshUrl('#targetBloc', array('blocRefresh'))); ?> you will refresh the bloc <span id="targetBloc">External bloc</span>

<h4>Code</h4>
<?php echo highlight_string("By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseAjaxRefreshUrl('#targetBloc', array('blocRefresh'))); ?> you will refresh the bloc <span id=\"targetBloc\">External bloc</span>", true)?>

<p>
	If you want to put unobstrusive javascript, you can use the regular function instead of the one with <b>Url</b> suffix<br/>
	For example, instead of using an url, you can create classic js calls using : 
	<?php echo highlight_string("By clicking on <?php echo Sweeml::link('this link', '#', array('onclick' => Sweeml::raiseAjaxRefresh('#targetBloc', array('blocRefresh')))); ?> you will refresh the bloc <span id=\"targetBloc\">External bloc</span>", true)?>
	
</p>

<h3>Javascript expansion on client side</h3>

<h4>Demo</h4>
By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseAjaxRefreshUrl(null, array('javascriptRefresh'))); ?> you will execute some js produced by the controller

<h4>Code</h4>
<?php echo highlight_string("By clicking on <?php echo Sweeml::link('this link', Sweeml::raiseAjaxRefreshUrl(null, array('javascriptRefresh'))); ?> you will execute some js produced by the controller", true)?>

<h2>Performing form validation using ajax Call</h2>

<h3>Classic form validation</h3>

<h4>Demo</h4>

<fieldset>
	<legend>Demo Form</legend>
	To see an error set a login shorter than 4 letters
	<?php echo Sweeml::beginAjaxForm(array('formSubmit')); ?>
		<?php $this->renderPartial('_form', array('demoForm' => $demoForm))?>
	<?php echo Sweeml::endForm(); ?>
</fieldset>

<h4>Code</h4>
<?php 
$code =<<<EOCODE
<fieldset>
	<legend>Demo Form</legend>
	To see an error set a login shorter than 4 letters
	<?php echo Sweeml::beginAjaxForm(array('formSubmit')); ?>
		<?php \$this->renderPartial('_form', array('demoForm' => \$demoForm))?>
	<?php echo Sweeml::endForm(); ?>
</fieldset>
EOCODE;
?>
<?php echo highlight_string($code, true)?>
