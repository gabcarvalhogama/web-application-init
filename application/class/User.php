<?php

	class User {
        
		/**
         * [checkPasswordHashed Check if the passwords match.]
         * @param  String $password
         * @param  String $dbPassword
         * @return Boolean
         */
        public function checkPasswordHashed($password, $dbPassword){
            return Bcrypt::check($password, $dbPassword);
        }
        
        /**
         * [getPasswordByEmail get user password using his email]
         * @param String $email 
         * @return String
         */
        public function getPasswordByEmail($email){
        	$sql = DB::open()->prepare("SELECT password FROM hz_users WHERE email = :email LIMIT 1");
        	$sql->execute([
        		":email" => filter_var($email, FILTER_SANITIZE_EMAIL)
        	]);
        	return ($sql->rowCount() > 0) ? $sql->fetchObject()->password : null;
        }

        /**
         * execute login
         * @param String $cpf 
         * @param String $password 
         * @return type
         */
        public function login($email, $password){
        	//!!!!!!!!!!!!!!! Verificar status, etc tudo aqui dps.
        	return $this->checkPasswordHashed($password, $this->getPasswordByEmail($email));
        }


        public function isUserAuthenticated(){
            if (session_status() == PHP_SESSION_NONE) session_start();
            
            return (!empty($_SESSION["email"]) AND !empty($_SESSION["password"])) ? $this->login($_SESSION["email"], $_SESSION["password"]) : false;
        }

        /**
         * [getUserByEmail get user informations using his email]
         * @param String $email 
         * @return String
         */
        public function getUserByEmail($email){
            $sql = DB::open()->prepare("SELECT u.iduser, u.fullname, u.photo, u.email, u.cpf, u.idagency, a.aglogo FROM hz_users u
               LEFT JOIN hz_agencies a ON a.idagency = u.idagency WHERE email = :email LIMIT 1");
            $sql->execute([
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL)
            ]);
            return ($sql->rowCount() > 0) ? $sql->fetchObject() : null;
        }

        public function getAgencyIdByUser($email){
            $sql = DB::open()->prepare("SELECT idagency FROM hz_users WHERE email = :email LIMIT 1");
            $sql->execute([
                ":email" => filter_var($email, FILTER_SANITIZE_EMAIL)
            ]);
            return ($sql->rowCount() > 0) ? $sql->fetchObject()->idagency : null;
        }

        // public function isUserFromAgency($email, $)

	}