<?php
// ajaxListaRemitosCliente.php
// muestra los remitos del cliente seleccionado.
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
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
    $stmt = odbc_exec2( $mssql, $sqlRemito, __LINE__, __FILE__);
    $stmt = odbc_exec2( $mssql, $sqlRemitoCancelado, __LINE__, __FILE__);
  }
  echo "yes";
} else {
  echo "No hay remitos seleccionados";
}



//echo $tabla;
?>
