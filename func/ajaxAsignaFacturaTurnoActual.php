<?php
// listaStockTanques.php
// muestra y lleva el control de stocks de combustibles.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
//print_r($_GET);
 // $array=array();
//$_POST['mes']='201411';
//IdMovimientoFac=' + IdMovimientoFac
if(isset($_GET['IdMovimientoFac'])&&is_numeric($_GET['IdMovimientoFac'])){
  // actualizo base
  $sql = "UPDATE dbo.movimientosDetalleFac SET ExcluidoDeTurno=0 WHERE IdMovimientoFac=$_GET[IdMovimientoFac]";

  $stmt = odbc_exec( $mssql, $sql);
  if( $stmt === false ){
      echo "1. Error in executing query.</br>$sql<br/>";
      die( print_r( sqlsrv_errors(), true));
  }
  echo "ok";
}

//echo $tabla;
?>
