<?php

if (PHP_SAPI === 'cli')
{
	$worker = new \eBuildy\Worker\CommandWorker();

	$worker->initialize($argv);

	return $worker->run();
}
else
{
	$worker = new \eBuildy\Worker\WebWorker();

	$worker->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);

	return $worker->run();
}