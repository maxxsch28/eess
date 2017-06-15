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
  $sql = "UPDATE dbo.movimientosDetalleFac SET ExcluidoDeTurno=0, IdCierreTurno = NULL WHERE IdMovimientoFac=$_GET[IdMovimientoFac]";
  fb($sql);
  $stmt = odbc_exec2( $mssql, $sql, __LINE__, __FILE__);
  echo "ok";
}

//echo $tabla;
?>
