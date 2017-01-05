<?php
// inicia session
session_start();

// incluye funciones
include($_SERVER['DOCUMENT_ROOT']."config/config.php");
include($root.'funciones/locale.php');
include($root.'funciones/login.php');
include($root.'funciones/funciones.php');

//debug
require_once('FirePHPCore/FirePHP.class.php');
require_once($root.'funciones/ChromePhp.php');

// firePHP DEBUG
$firephp = FirePHP::getInstance(true);
// $firephp-> *
require_once('FirePHPCore/fb.php');
// FB:: *
$firephp->setEnabled(false);  // or FB::
FB::send('prueba');


// chromePHP DEBUG
 ChromePhp::log('hello world');
 ChromePhp::log($_SERVER);
// using labels
foreach ($_SERVER as $key => $value) {
	 // ChromePhp::log($key, $value);
}	
// warnings and errors
ChromePhp::warn('this is a warning');
ChromePhp::error('this is an error');

/* @annotation: benchmark */
$tiempo = microtime();
if(isset($_GET['p'])&&!isset($_SESSION['partner'])){
	$_SESSION['partner']=$_GET['p'];
	$k=array_keys($CFG->partners, $_GET['p']);
	$_SESSION['idPartner']=$k[0];
}
?>