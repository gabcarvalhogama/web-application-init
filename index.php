<?php
	require_once "application/autoload.php";

	define("PATH", "http://site.local/");


	$router = new \Bramus\Router\Router();

	$router->get('/', function(){
		require_once "views/index.php";
	});

	$router->run();