eBuildy PHP Framework
=====================

Features
--------

- Simple and complete framework
- Dependency Injection container
- Annotations parser for routing and Dependency Injection
- Asset management with assets groups
- Templating with native PHP and/or Twig
- Input validators and form generator
- Simple console component
- Translates management
- Usefull helpers set (string, array, cryptage ...)
- No database override, use PDO

Get started
-----------

1. Install composer

	``curl -sS https://getcomposer.org/installer | php``

2. Declare composer dependencies (composer.json file)

	``{
		"require": {
			"ebuildy/ebuildy": "dev-master",
			"symfony/yaml" : "dev-master",
			"symfony/console" : "dev-master",
			"mikejestes/scheezy": "dev-master",
			"twig/twig" : "v1.14.1",
		}
	}``


The bootstrap (index.php)
-------------------------

Get ready! Create your index.php file like this:

	<?php

	define('DEBUG', true);

	define('ROOT', realpath(__DIR__.DIRECTORY_SEPARATOR.'..') . DIRECTORY_SEPARATOR);
	define('SOURCE_PATH', ROOT.'src/');
	define('VENDOR_PATH', ROOT.'vendor/');
	define('CONFIG_PATH', ROOT.'config/');
	define('TMP_PATH', ROOT . 'tmp/');
	define('WEB_PATH', ROOT.'web/');

	putenv('PATH=' . getenv('PATH') . ':/usr/local/bin:/usr/bin');
	
	header('Content-type: text/html; charset=UTF-8');

	include(VENDOR_PATH . 'autoload.php');
	include(SOURCE_PATH . 'MyApplication.php');

	$application = new MyApplication('production');

	$application->run();

	function debug($name, $value = null)
	{
		global $application;

		$application->container->getEbuildyDebugService()->log($name, $value);
	}