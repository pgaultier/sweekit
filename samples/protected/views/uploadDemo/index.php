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
?><h1><?php echo Sweeml::link('Main', array('site/'));?> > Plupload tools demo</h1>

<h2>Sweekit components used</h2>

<ul>
	<li>SwClientScriptBehavior : automatic script insertion</li>
	<li>SwRenderBehavior : return data with correct datatype</li>
	<li>SwUploadAction : upload temporary files</li>
	<li>SwDeleteAction : delete temporary uploaded files</li>
	<li>SwFileValidator : validate uploaded file</li>
</ul>

<h2>Upload two files</h2>

<h3>Upload ajax compliant</h3>

<h4>Demo</h4>

<fieldset>
	<legend>My simple upload form</legend>
	<?php echo Sweeml::beginForm();?>
		<?php echo Sweeml::errorSummary($demoFileForm); ?>
		<?php echo Sweeml::activeAsyncFileUpload($demoFileForm, 'file',array(
			'config' => array(
				'runtimes' => 'html5, flash',
				'auto' => true,
				'ui' => true,
				'maxFileSize' => '512mb',
			),
			'events'=>array(
				'beforeUpload' => 'js:function(up, file){ 
					$(\'#submitButton\').attr(\'disabled\', \'disabled\'); 
				}',
				'uploadComplete' => 'js:function(up, files){
					$(\'#submitButton\').removeAttr(\'disabled\'); 
				}',
			)
		));?>
		<?php echo Sweeml::htmlButton('submit', array('type' => 'submit', 'id' => 'submitButton')); ?>
	<?php echo Sweeml::endForm();?>
</fieldset>
<?php if(($savedFiles !== false) && (is_array($savedFiles) === true)):?>
<ul>
	<?php foreach($savedFiles as $i => $savedFile) : ?>
	<li><?php echo Sweeml::link($savedFile, $savedFile) ?></li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
<h4>Code</h4>

<?php 

$phpCode =<<<EOPHP
<fieldset>
	<legend>My simple upload form</legend>
	<?php echo Sweeml::beginForm();?>
		<?php echo Sweeml::errorSummary(\$demoFileForm); ?>
		<?php echo Sweeml::activeAsyncFileUpload(\$demoFileForm, 'file',array(
			'config' => array(
				'runtimes' => 'html5, flash',
				'auto' => true,
				'ui' => true,
				'maxFileSize' => '512mb',
			),
			'events'=>array(
				'beforeUpload' => 'js:function(up, file){ 
					$(\'#submitButton\').attr(\'disabled\', \'disabled\'); 
				}',
				'uploadComplete' => 'js:function(up, files){
					$(\'#submitButton\').removeAttr(\'disabled\'); 
				}',
			)
		));?>
		<?php echo Sweeml::htmlButton('submit', array('type' => 'submit', 'id' => 'submitButton')); ?>
	<?php echo Sweeml::endForm();?>
</fieldset>
EOPHP;

echo highlight_string($phpCode, true);


