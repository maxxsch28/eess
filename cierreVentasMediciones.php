<?php
if(substr($_SERVER['HTTP_USER_AGENT'], 0,4)=='curl'){
  //lo llame desde cron
} else {
  $nivelRequerido = 4;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');




// Grabo el estado de tanques a las 22 hs
// Saco el contenido del archivo de stock de teknivel
$output = shell_exec('rm /srv/www/htdocs/ypf/tmp/stocks.txt');
$output = shell_exec('smbget smb://maxx:e757g4aB@192.168.1.13/Teknivel/Tnq/stocks.txt -o /srv/www/htdocs/ypf/tmp/stocks.txt');
//var_dump($output);
$file = file_get_contents('tmp/stocks.txt');
//var_dump($file);
if(strpos($file, 'DESCARGA EN TANQUE')){
  $file3 = substr($file, 0, strpos($file, 'DESCARGA EN TANQUE'));
  $file2 = explode('Tanque', $file3);
} else {
  $file2 = explode('Tanque', $file);
}
//var_dump($file2);
$lecturaTanque = array();
foreach($tanques as $idTanque => $idArticulo){
  $lecturaTanque[$idTanque] = explode(',', $file2[$idTanque]);
  echo "Tanque $idTanque, {$lecturaTanque[$idTanque][8]} lts<br>";
  echo "Nivel tq $idTanque, {$lecturaTanque[$idTanque][5]} mm<br>";
  if(in_array($idTanque, $CFG->tanquesATomarMilimetrosDesdeTablas)){
    // calculo litros en base a los milímetros
    $sqlConversion = "SELECT tq$idTanque FROM `cierres_tanques_equivalencias` WHERE mm=".round($lecturaTanque[$idTanque][5],0).";";
    echo $sqlConversion;
    $result = $mysqli->query($sqlConversion);
    $litrosDesdeMM = $result->fetch_assoc();
    $tanque = "tq$idTanque";
    $lecturaTanque[$idTanque][8] = $litrosDesdeMM[$tanque];
  }
  if(!isset($tqs))$tqs = "'{$lecturaTanque[$idTanque][8]}'";
  else $tqs .= ", '{$lecturaTanque[$idTanque][8]}'";
  
}
//print_r($lecturaTanque);
$sqlCierreTanques = "INSERT INTO `pedidosypf`.`cierres_cem_tanques` (`fechaCarga`, `fechaCierre`, `turno`, `tq1`, `tq2`, `tq3`, `tq4`, `tq5`, `tq6`) VALUES (CURRENT_TIMESTAMP, '".date('Y-m-d 22:00:00')."', 'noche', $tqs);";
ChromePhp::log($sqlCierreTanques);
echo $sqlCierreTanques;
$result = $mysqli->query($sqlCierreTanques);



// Saco el estado de los tanques al último cierre de las 24
$sqlMediciones = "select IdArticulo, SUM(medicion) from dbo.CierresDetalleTanques where IdCierreTurno=(select top 1 IdCierreTurno from dbo.CierresTurno where IdCaja=1 AND idCierreTurno<=(select top 1 idCierreturno FROM dbo.Cierresturno where DATEPART(hh, Fecha)>=21 AND DATEPART(hh, Fecha)<=24 order by Fecha desc) order by fecha desc) GROUP BY IdArticulo";
$stmt = odbc_exec2( $mssql, $sqlMediciones, __FILE__, __LINE__);
while($rowMediciones = sqlsrv_fetch_array($stmt)){
  $mediciones[$rowMediciones[0]]=$rowMediciones[1];
}

// selecciono los dos turnos de las 24 de ayer y antes de ayer
$sqlTurnos = "select top 2 IdCierreTurno, fecha from dbo.CierresTurno where IdCaja=1 AND idCierreTurno<=(select top 1 idCierreturno FROM dbo.Cierresturno where DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=24 order by Fecha desc) AND  DATEPART(hh, Fecha)>=19 AND DATEPART(hh, Fecha)<=24 order by fecha desc";
$stmt = odbc_exec2( $mssql, $sqlTurnos, __FILE__, __LINE__);
while($rowTurnos = sqlsrv_fetch_array($stmt)){
  //print_r($rowTurnos);
  if(!isset($fechaCierre))$fechaCierre=$rowTurnos[1];
    $turnos[]=$rowTurnos[0];
}
//print_r($turnos);

// obtengo el estado de los aforadores al cierre de las 22 de ayer y antes de ayer
// Segun CaldenOil
foreach($turnos as $idcierreturno){
    $sqlAforadores = "select IdArticulo, sum(AforadorElectronico) as Electronico, sum(AforadorMecanico) as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno =$idcierreturno group by idarticulo";
    $stmt = odbc_exec2( $mssql, $sqlAforadores, __FILE__, __LINE__);
    while($rowAforadores = sqlsrv_fetch_array($stmt)){
        $electronicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[1];
        $mecanicos[$idcierreturno][$rowAforadores[0]]=$rowAforadores[2];
    }
    $sqlAforadores2 = "select dbo.CierresDetalleSurtidores.idManguera, AforadorElectronico as Electronico, AforadorMecanico as Mecanico from dbo.CierresDetalleSurtidores, dbo.CierresSurtidores, dbo.mangueras where dbo.CierresSurtidores.IdCierreSurtidores=dbo.CierresDetalleSurtidores.IdCierreSurtidores AND dbo.mangueras.idmanguera=dbo.cierresdetallesurtidores.idManguera AND IdCierreTurno =$idcierreturno";
    //echo $sqlAforadores2;
    $stmt2 = odbc_exec2( $mssql, $sqlAforadores2, __FILE__, __LINE__);
    while($rowAforadores2 = sqlsrv_fetch_array($stmt2)){
        $electronicos2[$idcierreturno][$rowAforadores2[0]]=$rowAforadores2[1];
        $mecanicos2[$idcierreturno][$rowAforadores2[0]]=$rowAforadores2[2];
    }
}

// para stock
// obtengo los aforadores según CEM del último registro de mysqli
$sqlUltimoRegistro = "SELECT * FROM cierres_calden_aforadores WHERE idCierreturno=$turnos[1]";
//echo "$sqlUltimoRegistro<br>";
$result = $mysqli->query($sqlUltimoRegistro);
$ultimosAforadores = $result->fetch_assoc();
//print_r($ultimosAforadores);
$sqlGrabaAforadoresDiarios = "INSERT INTO cierres_calden_aforadores (idCierreTurno, fechaCarga, fechaCierre, ed1, ns1, ni1, ed2, ns2, ni2, ud3, ed4, ud5, ud6, ed7) values ($turnos[0], now(), '".date_format($fechaCierre, 'Y-m-d 22:00:00')."'";
foreach($electronicos2[$turnos[1]] as $pico => $aforador) {
  $sqlGrabaAforadoresDiarios .=", '".($ultimosAforadores[$arrayPicosNumeros[$pico]]+($electronicos2[$turnos[0]][$pico]-$aforador))."'";
  // calculé cuanto fue la venta por diferencia de aforadores.
  echo "<br>Calculo: $pico -> :".($ultimosAforadores[$arrayPicosNumeros[$pico]]+($electronicos2[$turnos[0]][$pico]-$aforador))." lts<br/>";
}
$sqlGrabaAforadoresDiarios .=");";
echo $sqlGrabaAforadoresDiarios.'<br>';
$result = $mysqli->query($sqlGrabaAforadoresDiarios);



// cruza grabaciones, graba ventas diarias por producto
$sqlGrabaVentaDiaria = "INSERT INTO ventasDiarias (ed, ud, np, ns, fecha, diaSemana) VALUES (";
foreach($articulo as $idArticulo => $producto){
    $ventaElectronica[$idArticulo]=round($electronicos[$turnos[0]][$idArticulo]-$electronicos[$turnos[1]][$idArticulo],2);
    $ventaMecanica[$idArticulo]=round($mecanicos[$turnos[0]][$idArticulo]-$mecanicos[$turnos[1]][$idArticulo],2);
    $sqlGrabaVentaDiaria .= "$ventaElectronica[$idArticulo], ";
    if($ventaElectronica[$idArticulo]<>$ventaMecanica[$idArticulo])$existenDiferencias=true;
}
$sqlGrabaVentaDiaria.="'".date_format($fechaCierre, 'Y-m-d H:i:s')."', '".date_format($fechaCierre, 'N')."');";
$result = $mysqli->query($sqlGrabaVentaDiaria);


$ultimaFechaCargada = new DateTime();
// calculo los litros YER facturados, sirve para comparar contra lo del cierres_cem_aforadores
$sqlYER = "select IdArticulo, SUM(Cantidad) as q from dbo.MovimientosFac, dbo.MovimientosDetalleFac where dbo.MovimientosFac.IdMovimientoFac=dbo.MovimientosDetalleFac.IdMovimientoFac and IdCliente=1283 and Fecha<='".date("Y/m/d 22:00:00")."' AND Fecha>'".$ultimaFechaCargada->modify('-1 day')->format('Y/m/d')." 22:00:00' group by IdArticulo";
echo $sqlYER;
$stmt = odbc_exec2($mssql, $sqlYER, __FILE__, __LINE__);
$arrayYER = array();
$sqlGrabaYER = "INSERT INTO yer (fecha, despachos, ed, ud, np, ns) VALUES ('".date("Y/m/d 22:00:00")."', 1, ";
while($rowYER = sqlsrv_fetch_array($stmt)){
  $arrayYER[$rowYER['IdArticulo']] = $rowYER['q'];
}
sort($arrayYER);
sort($articulo);
foreach($articulo as $key=>$producto){
  if(isset($arrayYER[$key])){
    $sqlGrabaYER .= "'-$arrayYER[$key]', ";
  } else {
    $sqlGrabaYER .= "0, ";
  }
}
$sqlGrabaYER = substr($sqlGrabaYER,0,-2).");";
ChromePhp::log($sqlGrabaYER);
$result = $mysqli->query($sqlGrabaYER);
ChromePhp::log($arrayYER);
?>
