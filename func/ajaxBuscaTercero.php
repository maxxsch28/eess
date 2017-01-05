<?php
// cargaNuevosCelulares.php
// recibe datos del form y los procesa en mysql
include('../include/inicia.php');
 // print_r($_POST);
 // $array=array();
if(isset($_GET['term'])){
  $sqlClientes = "SELECT idTercero, razonSocial, cuit, condicion FROM iva_terceros WHERE razonsocial LIKE ('%$_GET[term]%') OR cuit LIKE ('%$_GET[term]%')";
  $result = $mysqli->query($sqlClientes);
  $array = "[";
  while($row = $result->fetch_assoc()){
    $array.="{\"label\":\"$row[razonSocial]\", \"cuit\":\"$row[idTercero]\", \"value\":\"$row[cuit]\"},";
    if(!isset($_SESSION['datosTercero'][$row['idTercero']]))$_SESSION['datosTercero'][$row['idTercero']] = $row['cuit'];
  }
  $_SESSION['datosTercero'][$row['idTercero']] = $row['cuit'];
  $array = substr($array,0,-1)."]";
  echo($array);
}
?>
