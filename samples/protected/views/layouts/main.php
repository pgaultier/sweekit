<?php
/**
 * main.php
 *
 * PHP version 5.2+
 *
 * main layout file
 *
 * @author    Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2012 Sweelix
 * @license   http://www.sweelix.net/license license
 * @version   1.10.0
 * @link      http://www.sweelix.net
 * @category  views
 * @package   Sweeml.samples.views.layout
 * @since     1.9.0
 */
?><!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="language" content="en" />
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	</head>
	<body>
		<?php echo $content; ?>
	</body>
</html>