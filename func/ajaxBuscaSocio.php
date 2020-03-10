<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
// print_r($_GET);
 // $array=array();
if(isset($_GET['term'])){
  $sqlClientes = "SELECT fletero, nombre Collate SQL_Latin1_General_CP1253_CI_AI as nombre, cuit, tipo_resp, cliente, direccion Collate SQL_Latin1_General_CP1253_CI_AI as direccion FROM fleteros WHERE (rehabfecha>=inhabfecha AND rehabhora>=inhabhora) AND nombre LIKE ('%$_GET[term]%') OR fletero LIKE ('%$_GET[term]%')";
  ChromePHP::log($sqlClientes);
  
  $stmt = odbc_exec2( $mssql2, $sqlClientes, __LINE__, __FILE__);
  if($stmt)sqlsrv_num_rows($stmt);

  $array = "[";
  
  while($row = sqlsrv_fetch_array($stmt)){
    //echo "$fila[celular]";
    ChromePhp::log($row);
    $array.="{\"label\":\"".trim($row['nombre'])."\", \"value\":\"$row[fletero]\", \"cuit\":\"$row[cuit]\", \"iva\":\"$row[tipo_resp]\", \"domicilio\":\"".trim($row['direccion'])."\" },";
    
    if(!isset($_SESSION['datosSocio'][$row['fletero']]))$_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
  }
  $_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
  $array = substr($array,0,-1)."]";
  echo($array);
}
if(isset($_POST['idCliente'])){
  $sqlClientes = "select RazonSocial, Identificador from dbo.Clientes where IdCliente='$_POST[idCliente]'";
  $stmt = odbc_exec2( $mssql, $sqlClientes, __LINE__, __FILE__);
  $arrayReemplazo = array('TELEFONO', 'TELEFONOS');
  $row = sqlsrv_fetch_array($stmt);
  if(trim(str_replace($arrayReemplazo, '', $row['Identificador']))<>''){
          $identificador = "(".str_replace($arrayReemplazo, '', $row['Identificador']).")";
  } else $identificador = "";
  echo "$row[RazonSocial] ".trim($identificador);
}
?>
