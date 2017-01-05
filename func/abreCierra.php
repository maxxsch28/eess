<?php
// calculaPromedios.php
include_once('../include/inicia.php');

$sqlTurnos = "SELECT dbo.CierresTurno.IdCierreCajaTesoreria, dbo.Table_1.idCierreCajaTesoreria FROM dbo.CierresTurno, dbo.Table_1 WHERE dbo.CierresTurno.IdCierreTurno=dbo.Table_1.idCierreTurno AND dbo.CierresTurno.IdCierreTurno=$_GET[idTurno];";

$stmt = sqlsrv_query( $mssql, $sqlTurnos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlTurnos<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$rowTurnos = sqlsrv_fetch_array($stmt);
if($rowTurnos[0]==$rowTurnos[1]){
	// abrir
	$update = "UPDATE dbo.CierresTurno set idCierreCajaTesoreria=null WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span class='abreCierra label label-warning'><i class='glyphicon glyphicon-warning-sign'></i>CERRAR</span>";
} else {
	// cerrar
	$update = "UPDATE dbo.CierresTurno set idCierreCajaTesoreria=$rowTurnos[1] WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span class='abreCierra label label-success'>ABRIR</span>";
}

$stmt = sqlsrv_query( $mssql, $update);
echo $devuelve;
?>