<?php
// ajaxReimputaReciboCajaAdministracion.php
// Cambia el recibo seleccionado a la caja de Administracion abierta en curso
include('../include/inicia.php');
print_r($_GET);

if(isset($_GET['IdRecibo'])&&is_numeric($_GET['IdRecibo'])){
  $idRecibo = $_GET['IdRecibo'];
  $sqlRecibo = "UPDATE dbo.recibos SET IdCaja=4 WHERE IdRecibo='$idRecibo'";
  $sqlChequesEnRecibo = "UPDATE dbo.chequesterceros SET IdCaja=4, IdUbicacion=8 WHERE IdRecibo='$idRecibo'";
  $sqlChequesEnRecibo = "UPDATE dbo.chequesterceros SET IdCaja=4 WHERE IdRecibo='$idRecibo'";
  fb($sqlRecibo);
  fb($sqlChequesEnRecibo);
  $stmt = sqlsrv_query( $mssql, $sqlRecibo);
  $stmt = sqlsrv_query( $mssql, $sqlChequesEnRecibo);
  echo "yes";
} else {
  echo "No hay remitos seleccionados";
}



//echo $tabla;
?>
