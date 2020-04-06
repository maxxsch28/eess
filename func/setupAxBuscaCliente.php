<?php
// setupAxBuscaCliente.php
// permite seleccionar clientes buscando por nombre
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
// print_r($_GET);
if(isset($_GET['term'])){
  $sqlClientes = "SELECT codigo, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre FROM clientes WHERE (rehabfecha>=inhabfecha AND rehabhora>=inhabhora) AND nombre LIKE ('%$_GET[term]%');";
//ChromePHP::log($sqlClientes);
  
  $stmt = odbc_exec2( $mssql2, $sqlClientes, __LINE__, __FILE__);
  if($stmt)sqlsrv_num_rows($stmt);

  $array = "[";
  
  while($row = sqlsrv_fetch_array($stmt)){
    //ChromePhp::log($row);
    $array.="{\"label\":\"".trim($row['nombre'])."\", \"codigo\":\"$row[codigo]\", \"value\":\"".trim($row['nombre'])."\"},";
  }
  $array = substr($array,0,-1)."]";
  echo($array);
}

?>
