eBuildy PHP Framework
=====================

Features
--------

- Simple and complete framework
- [Dependency Injection container](https://github.com/ebuildy/ebuildy/wiki/Container-builder)
- [PHP Annotations parser for Routing, Dependency Injection..](https://github.com/ebuildy/ebuildy/wiki/Annotation-parser)
- [Asset management with assets groups](https://github.com/ebuildy/ebuildy/wiki/Asset-management)
- Templating with native PHP and/or Twig
- Input validators and form generator
- Simple console component
- Translates service
- [Hook service](https://github.com/ebuildy/ebuildy/wiki/Hook-service)
- Usefull helpers set (string, array, cryptage ...)
- No database override, use PDO

Get started
-----------

1. Install composer

	``curl -sS https://getcomposer.org/installer | php``

2. Declare composer dependencies (composer.json file)

    ```json
    {
		"require": {
			"ebuildy/ebuildy": "dev-master",
			"symfony/yaml" : "dev-master",
			"symfony/console" : "dev-master",
			"mikejestes/scheezy": "dev-master",
			"twig/twig" : "v1.14.1",
		}
    }


The bootstrap (index.php)
-------------------------

Get ready! Create your index.php file like this:

```php
<?php

define('DEBUG', isset($_GET['debug']));

define('ROOT', realpath(__DIR__.DIRECTORY_SEPARATOR.'..') . DIRECTORY_SEPARATOR);
define('SOURCE_PATH', ROOT.'src/');
define('VENDOR_PATH', ROOT.'vendor/');
define('CONFIG_PATH', ROOT.'config/');
define('KENV', $_SERVER['ENV']);
define('TMP_PATH', ROOT . 'tmp/' . KENV . '/');
define('WEB_PATH', ROOT.'web/');

putenv('PATH=' . getenv('PATH') . ':/usr/local/bin:/usr/bin');

include(VENDOR_PATH . 'autoload.php');
        
if (PHP_SAPI === 'cli')
{
	 if (defined("__CONFIGURATION_MODE__"))
	 {
		$configuration = new \eBuildy\Container\ContainerBuilder();

		$configuration->loadFile(CONFIG_PATH . KENV . '/config.yml')
				->loadAnnotations(VENDOR_PATH . 'ebuildy/ebuildy/src')
				->loadAnnotations(SOURCE_PATH . 'Kinou', SOURCE_PATH)
				->build(TMP_PATH, 'Container');

		die("Configuration has been done !");
	}
	
	include(TMP_PATH . "Container.php");

	$worker = new \eBuildy\Worker\CommandWorker(new \Container());

	$worker->initialize($argv);

	return $worker->run();
}
else
{
	include(TMP_PATH . "Container.php");
	
	$worker = new \eBuildy\Worker\WebWorker(new \Container());

	$worker->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);

	return $worker->run();
}
