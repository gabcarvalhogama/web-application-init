<?php
	class DB{
		public static function open(){
	        try{
	            return new PDO("mysql:host=localhost;dbname=haza;charset=utf8", "root", "", array(PDO::MYSQL_ATTR_INIT_COMMAND => 'set lc_time_names="pt_BR"'));
	        }catch(PDOException $e){
	            error_log("Failed to connect to database: ".$e);
	        }
	    }
	}