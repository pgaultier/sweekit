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
 * @package   Sweeml.samples.views.protocolDemo
 * @since     1.9.0
 */
?><h1><?php echo Sweeml::link('Main', array('site/'));?> > Protocol filtering demo</h1>

<h2>Sweekit components used</h2>

<ul>
	<li>SwProtocolFilter : check and change protocol used for a page</li>
</ul>

<h2>Test pages</h2>

<p>The protocol filter do not change the urls but redirect the pages using correct protocol</p>

<h3>Links</h3>

<h4>Demo</h4>

<ul>
	<li>Access to page with <?php echo Sweeml::link('no protocol forced', array('index'))?> (can be http or https)</li>
	<li>Access to <?php echo Sweeml::link('secured', array('secured'))?> page (must be https)</li>
	<li>Access to <?php echo Sweeml::link('classic', array('classic'))?> page (must be http)</li>
</ul>

<h4>Code</h4>

<?php 
$phpCode =<<<EOPHP
<?php
	// controller stuff
	
	/**
	 * Add filters to current controller
	 *
	 * @return array
	 */
	public function filters() {
		return array(
				array(
						'ext.sweekit.filters.SwProtocolFilter + secured',
						'mode' => 'https',
				),
				array(
						'ext.sweekit.filters.SwProtocolFilter + classic',
						'mode' => 'http',
				),
		);
	}
EOPHP;
	echo highlight_string($phpCode, true);

