<?php
// ajaxReimputaReciboCajaAdministracion.php
// Cambia el recibo seleccionado a la caja de Administracion abierta en curso
include(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
print_r($_GET);

if(isset($_GET['IdRecibo'])&&is_numeric($_GET['IdRecibo'])){
  $idRecibo = $_GET['IdRecibo'];
  $sqlRecibo = "UPDATE dbo.recibos SET IdCaja=4 WHERE IdRecibo='$idRecibo'";
  $sqlChequesEnRecibo = "UPDATE dbo.chequesterceros SET IdCaja=4, IdUbicacion=8 WHERE IdRecibo='$idRecibo'";
  $sqlChequesEnRecibo = "UPDATE dbo.chequesterceros SET IdCaja=4 WHERE IdRecibo='$idRecibo'";
  ChromePhp::log($sqlRecibo);
  ChromePhp::log($sqlChequesEnRecibo);
  $stmt = odbc_exec( $mssql, $sqlRecibo);
  $stmt = odbc_exec( $mssql, $sqlChequesEnRecibo);
  echo "yes";
} else {
  echo "No hay remitos seleccionados";
}



//echo $tabla;
?>
