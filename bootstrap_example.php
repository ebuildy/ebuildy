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