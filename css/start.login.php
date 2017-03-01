<?php
/* @annotation: funciones/login.php */
require($_SERVER['DOCUMENT_ROOT']."/funciones/ldap.php");
require($_SERVER['DOCUMENT_ROOT']."/funciones/datos_usuarios.php");



function confirmUser($username, $password){
	/* Add slashes if necessary (for query) */
	if(!get_magic_quotes_gpc()){
		$username = addslashes($username);
	}
	
	/* Verify that user is in database */
	
	$result = ldap_loguin($username, $password);
	
	if($result){
		privilegios($username);
		return 0; //Success! Username and password confirmed
	}else{
		return 2; //Indicates password failure
	}
}

/*
 * checkLogin - Checks if the user has already previously
 * logged in, and a session with the user has already been
 * established. Also checks to see if user has been remembered.
 * If so, the database is queried to make sure of the user's
 * authenticity. Returns true if the user has logged in.
*/
function checkLogin(){
	/* Check if user has been remembered */
	if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass'])){
		$_SESSION['username'] = $_COOKIE['cookname'];
		$_SESSION['password'] = $_COOKIE['cookpass'];
		if(isset($_COOKIE['cookmter']))$_SESSION['master'] = $_COOKIE['cookmter'];
	}
	
	/* Username and password have been set */
	if(isset($_SESSION['username']) && isset($_SESSION['password'])){
		/* Confirm that username and password are valid */
		if(confirmUser($_SESSION['username'], $_SESSION['password']) != 0){
			/* Variables are incorrect, user not logged in */
 			unset($_SESSION['username']);
 			unset($_SESSION['password']);
 			unset($_SESSION);
			return false;
		}
		return true;
	}else{
		/* User not logged in */
		return false;
	}
}

function logout(){
	/* Kill session variables */
	if(loguea("logoutweb", $_SESSION['username'])){
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		$_SESSION = array(); // reset session array
		session_destroy();   // destroy session.
	}
}


/* Sets the value of the logged_in variable, which can be used in your code */
$logged_in = checkLogin();
?>