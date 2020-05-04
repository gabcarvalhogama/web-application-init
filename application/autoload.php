<?php
	#Autoload composer
	require_once "vendor/autoload.php";

	spl_autoload_register(function($class){
        if(file_exists(__DIR__ . "/class/$class.php"))
            require_once __DIR__ . "/class/$class.php";
    });
