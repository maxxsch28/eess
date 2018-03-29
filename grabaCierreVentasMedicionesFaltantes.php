<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');



// selecciono todos los turnos de las 22 del mes
$sqlTurnos = "SELECT IdCierreTurno, fecha FROM dbo.CierresTurno WHERE IdCaja=1 AND DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=23 AND fecha>='".date('Y-m-d', strtotime('last day of previous month'))."' ORDER BY fecha DESC";
//$sqlTurnos = "SELECT IdCierreTurno, fecha FROM dbo.CierresTurno WHERE IdCaja=1 AND DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=23 AND fecha>='2017-12-01' ORDER BY fecha DESC";
//$sqlTurnos = "SELECT IdCierreTurno, fecha FROM dbo.CierresTurno WHERE IdCaja=1 AND DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=23 and Fecha>'".date("Y")."-02-01' ORDER BY fecha DESC";
$stmt = odbc_exec2( $mssql, $sqlTurnos, __FILE__, __LINE__);
while($rowTurnos = sqlsrv_fetch_array($stmt)){
    if(!isset($fechaCierre))$fechaCierre=$rowTurnos[1];
	$turnos[]=$rowTurnos[0];
    $fecha[]=$rowTurnos[1];
}

$mysqli->query("INSERT INTO ultimaactualizacion (fecha, tipo) VALUES (now(), 'automatica')");
$sqlDiasCalden = "select count(DISTINCT convert(date, Fecha)) as Date from dbo.Despachos";
$stmt = odbc_exec2($mssql, $sqlDiasCalden, __FILE__, __LINE__);

$rowDiasCalden = sqlsrv_fetch_array($stmt);
$diasCalden = $rowDiasCalden[0];

$sqlPromedioHistorico = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/$diasCalden from dbo.Despachos  group by  datepart(HOUR, Fecha) order by hora";
// ChromePhp::log($sqlPromedioHistorico);
$stmt2 = odbc_exec2( $mssql, $sqlPromedioHistorico, __FILE__, __LINE__);
while($row2 = sqlsrv_fetch_array($stmt2)){
  $sqlInsert = "INSERT INTO [coop].[dbo].[despachosPromedio] (hora, despachos) VALUES ($row2[0], $row2[1]);";
  echo "\n\n".$sqlInsert."\n\n";
  $stmt3 = odbc_exec2( $mssql, $sqlInsert, __FILE__, __LINE__);
}
// saca promedio general desde el día 0 hasta hoy
// select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/1617 from dbo.Despachos  group by  datepart(HOUR, Fecha) order by hora



// obtengo el estado de los aforadores al cierre de las 22 de ayer y antes de ayer
foreach($turnos as $idcierreturno){
    $sqlAforadores = "select IdArticulo, sum(AforadorElectronico) as Electronico, sum(AforadorMecanico) as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno=$idcierreturno group by idarticulo";
    $stmt = odbc_exec2( $mssql, $sqlAforadores, __FILE__, __LINE__);
    while($rowAforadores = sqlsrv_fetch_array($stmt)){
        $electronicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[1];
        $mecanicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[2];
    }
}
foreach($turnos as $key => $idcierreturno){
    $sqlGrabaVentaDiaria = "INSERT INTO ventasDiarias (ed, ud, np, ns, fecha, diaSemana) VALUES (";
    foreach($articulo as $idArticulo => $producto){
	if(isset($turnos[$key + 1])){
        $ventaElectronica[$idArticulo]=round($electronicos[$turnos[$key]][$idArticulo]-$electronicos[$turnos[$key+1]][$idArticulo],2);
	}
        //echo "idarti $idArticulo || {$turnos[$key]} || $key";
	$sqlGrabaVentaDiaria .= "$ventaElectronica[$idArticulo], ";
    }
    $dia = $fecha[$key]->format("Y-m-d");
    $sqlGrabaVentaDiaria.="'$dia', ".date("N", strtotime($dia)).");";
    echo "\n\n".$sqlGrabaVentaDiaria."<br>";
    $result = $mysqli->query($sqlGrabaVentaDiaria);
}

