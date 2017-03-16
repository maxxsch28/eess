<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

$sqlTurnos = "SELECT dbo.CierresTurno.IdCierreCajaTesoreria, dbo.Table_1.idCierreCajaTesoreria FROM dbo.CierresTurno, dbo.Table_1 WHERE dbo.CierresTurno.IdCierreTurno=dbo.Table_1.idCierreTurno AND dbo.CierresTurno.IdCierreTurno=$_GET[idTurno];";

$stmt = odbc_exec2( $mssql, $sqlTurnos, __LINE__, __FILE__);
$rowTurnos = sqlsrv_fetch_array($stmt);
if($rowTurnos['IdCierreCajaTesoreria']==$rowTurnos['idCierreCajaTesoreria']){
	// abrir
	$update = "UPDATE dbo.CierresTurno set idCierreCajaTesoreria=null WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span class='abreCierra label label-warning'><i class='glyphicon glyphicon-warning-sign'></i>CERRAR</span>";
} else {
	// cerrar
	$update = "UPDATE dbo.CierresTurno set idCierreCajaTesoreria=$rowTurnos[idCierreCajaTesoreria] WHERE IdCierreTurno=$_GET[idTurno];";
	$devuelve = "<span class='abreCierra label label-success'>ABRIR</span>";
}

$stmt = odbc_exec2( $mssql, $update, __LINE__, __FILE__);
echo $devuelve;
?>
