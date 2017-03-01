<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
 // print_r($_POST);
 // $array=array();
if(isset($_GET['term'])){
  // borro todos los registros de este cliente
  //$sqlClientes = "SELECT * FROM `iva_socios` WHERE (razonsocial LIKE ('%$_GET[term]%') OR codigo LIKE ('%$_GET[term]%') OR cuit LIKE ('%$_GET[term]%')) ORDER BY razonsocial;";
  $sqlClientes = "SELECT fletero, nombre, cuit, tipo_resp, cliente FROM fleteros WHERE (rehabfecha>=inhabfecha AND rehabhora>=inhabhora) AND nombre LIKE ('%$_GET[term]%') OR fletero LIKE ('%$_GET[term]%')";
  //echo $sqlOrdenes;
  
  $stmt = odbc_exec( $mssql2, $sqlClientes);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlClientes<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
   $array = "[";
  while($row = odbc_fetch_array($stmt)){
    //echo "$fila[celular]";
    $array.="{\"label\":\"$row[nombre]\", \"value\":\"$row[fletero]\"},";
    if(!isset($_SESSION['datosSocio'][$row['fletero']]))$_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
    //[ { "id": "Sylvia borin", "label": "Garden Warbler", "value": "Garden Warbler" }, { "id": "Anas querquedula", "label": "Garganey", "value": "Garganey" } ]
  }
  $_SESSION['datosSocio'][$row['fletero']] = $row['cuit'];
  $array = substr($array,0,-1)."]";
  echo($array);
}
if(isset($_POST['idCliente'])){
  $sqlClientes = "select RazonSocial, Identificador from dbo.Clientes where IdCliente='$_POST[idCliente]'";
  $stmt = odbc_exec( $mssql, $sqlClientes);
  $arrayReemplazo = array('TELEFONO', 'TELEFONOS');
  $row = odbc_fetch_array($stmt);
  if(trim(str_replace($arrayReemplazo, '', $row['Identificador']))<>''){
          $identificador = "(".str_replace($arrayReemplazo, '', $row['Identificador']).")";
  } else $identificador = "";
  echo "$row[RazonSocial] $identificador";
}
?>
