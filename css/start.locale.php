<?php
/* @annotation: Localizacion */
if(!isset($_SESSION['idioma'])&&isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	if(strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], ","))
		$idioma=substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'],","));
	else
		$idioma=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
} else {
	$idioma="es_AR";
}

$idioma = $_SESSION['idioma'] = strtr($idioma, "-", "_");
$idioma = "es_AR";
$locale = setlocale(LC_ALL, $idioma);
bindtextdomain("dbank", $_SERVER['DOCUMENT_ROOT']."locale");
textdomain("dbank");
$CFG->idioma=substr($idioma,0,2);
?>