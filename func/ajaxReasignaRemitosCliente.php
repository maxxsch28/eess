<?php
// ajaxListaRemitosCliente.php
// muestra los remitos del cliente seleccionado.
include('../include/inicia.php');
//print_r($_GET);

if(!isset($_GET['nuevoCliente'])||!is_numeric($_GET['nuevoCliente'])){
  echo "Seleccione el nuevo cliente";
  die;
}

if(isset($_GET['idRemitos'])){
  $idRemitos = explode(',',$_GET['idRemitos']);
  foreach($idRemitos as $IdMovimientoFac){
    $sqlRemito = "UPDATE dbo.MovimientosFac SET idCliente='$_GET[nuevoCliente]' WHERE IdMovimientoFac=$IdMovimientoFac;";
    $sqlRemitoCancelado = "UPDATE dbo.MovimientosFac SET idCliente='$_GET[nuevoCliente]' WHERE IdMovimientoCancelado=$IdMovimientoFac;";
    $stmt = sqlsrv_query( $mssql, $sqlRemito);
    $stmt = sqlsrv_query( $mssql, $sqlRemitoCancelado);
  }
  echo "yes";
} else {
  echo "No hay remitos seleccionados";
}



//echo $tabla;
?>