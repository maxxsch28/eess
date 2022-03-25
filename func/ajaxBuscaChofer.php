<?php
// ajaxBuscaChofer.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
// print_r($_GET);
 // $array=array();
if(isset($_GET['term'])){
  $sqlClientes = "SELECT a.codigo, a.nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, b.nombre Collate SQL_Latin1_General_CP1253_CI_AI as fletero FROM choferes a, fleteros b WHERE a.fletero=b.fletero AND (a.rehabfecha>=a.inhabfecha AND a.rehabhora>=a.inhabhora) AND a.nombre LIKE ('%$_GET[term]%')";
  ChromePHP::log($sqlClientes);
  
  $stmt = odbc_exec2( $mssql2, $sqlClientes, __LINE__, __FILE__);
  if($stmt)sqlsrv_num_rows($stmt);

  $array = "[";
  
  while($row = sqlsrv_fetch_array($stmt)){
    //echo "$fila[celular]";
    ChromePhp::log($row);
    $array.="{\"label\":\"".trim($row['nombre'])."\", \"value\":\"$row[codigo]\" },";
    
    if(!isset($_SESSION['datosSocio'][$row['fletero']]))$_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
  }
  $_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
  $array = substr($array,0,-1)."]";
  echo($array);
}
?>