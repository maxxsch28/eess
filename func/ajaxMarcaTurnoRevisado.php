<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$sqlTurnos = "SELECT dbo.Table_1.lotesRevisados FROM dbo.Table_1 WHERE dbo.Table_1.IdCierreTurno=$_GET[idTurno];";

$stmt = odbc_exec( $mssql, $sqlTurnos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlTurnos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$rowTurnos = odbc_fetch_array($stmt);
if($rowTurnos[0]==0||$rowTurnos[0]==NULL){
	// cerrar
	$update = "UPDATE dbo.Table_1 SET lotesRevisados=1 WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span id='marcarRevisado' class='btn btn-xs btn-success'>REVISADO</span>";
} else {
	// abrir
	$update = "UPDATE dbo.Table_1 SET lotesRevisados=0 WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span id='marcarRevisado' class='btn btn-xs btn-danger'>NO REVISADO</span>";
}

$stmt = odbc_exec( $mssql, $update);
echo $devuelve;
?>