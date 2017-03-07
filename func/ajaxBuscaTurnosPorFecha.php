<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

//fb($_REQUEST);

if(!isset($_SESSION['empleados'])){
  $s = "SELECT IdEmpleado, Empleado FROM empleados ORDER BY IdEmpleado";
  $q = odbc_exec2($mssql, $s, __LINE__, __FILE__);
  while($r = odbc_fetch_array($q)){
    $rr[$r['IdEmpleado']]=$r['Empleado'];
  }
  $_SESSION['empleados']=$rr;
}

$andFecha=(isset($_REQUEST['rangoInicio']))?" AND Fecha>='$_REQUEST[rangoInicio]' AND Fecha<='$_REQUEST[rangoFin] 23:59:59'":'';
if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
  $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
  $andFecha=" AND Fecha>='$_REQUEST[mes]' AND Fecha<='$mesFin'";
}

$soloabiertos = (isset($_REQUEST['soloabiertos'])&&$_REQUEST['soloabiertos']==1)?" AND dbo.CierresTurno.IdCierreCajaTesoreria is null":'';
$soloNoRevisados = "";
if($_POST['buscador']=='tarjetas'){
  if(isset($_POST['solonorevisados'])&&$_POST['solonorevisados']==1){
    $soloNoRevisados = " AND dbo.Table_1.lotesRevisados=1";
  } else if (isset($_POST['solonorevisados'])&&$_POST['solonorevisados']==2){
    $soloNoRevisados = " AND (dbo.Table_1.lotesRevisados=0 OR dbo.Table_1.lotesRevisados IS NULL)";
  } else {
    $soloNoRevisados = "";
  }
}

$factdif = (isset($_REQUEST['factdif'])&&$_REQUEST['factdif']==1)?" AND dbo.CierresTurno.EmitioFacturaComplemento=0":'';

$idCaja = (isset($_REQUEST['idCaja'])&&$_REQUEST['idCaja']>0)?" AND IdCaja=$_REQUEST[idCaja]":'';

if(is_array($_REQUEST['idCierreCajaTesoreria'])&&$_REQUEST['idCierreCajaTesoreria'][0]<>''){
  $cierresTurnos = " AND dbo.CierresTurno.IdCierreCajaTesoreria IN (";
  foreach($_REQUEST['idCierreCajaTesoreria'] as $turno){
    $cierresTurnos .= "$turno, ";
  }
  $cierresTurnos = substr($cierresTurnos, 0, -2).")";
} else $cierresTurnos = "";

// levanta datos que faltan en tabla temporal
$sqlTurnos0 = "SELECT IdCierreTurno, IdCierreCajaTesoreria FROM dbo.CierresTurno WHERE dbo.CierresTurno.IdCierreTurno NOT IN (SELECT idCierreTurno FROM dbo.Table_1 where IdCierreTurno IS NOT NULL);";

$sqlTurnos0 = "SELECT IdCierreTurno, IdCierreCajaTesoreria FROM dbo.CierresTurno WHERE dbo.CierresTurno.IdCierreTurno IN (SELECT idCierreTurno FROM dbo.Table_1 where IdCierreCajaTesoreria IS NULL);";
$stmt0 = odbc_exec2( $mssql, $sqlTurnos0, __LINE__, __FILE__);
//fb($sqlTurnos0);
while($rowTurnos0 = odbc_fetch_array($stmt0)){
  //fb($rowTurnos0);var_dump($rowTurnos0);
  if(isset($rowTurnos0['IdCierreCajaTesoreria'])){
    $dmd = odbc_fetch_array(odbc_exec( $mssql, "SELECT idCierreCajaTesoreria FROM dbo.Table_1 WHERE IdCierreTurno=$rowTurnos0[IdCierreTurno]"));
    //var_dump($dmd);
    fb("SELECT idCierreCajaTesoreria FROM dbo.Table_1 WHERE IdCierreTurno=$rowTurnos0[IdCierreTurno]");
    if($dmd == NULL){
      $sqlInsert = "INSERT INTO dbo.Table_1 (idCierreTurno, idCierreCajaTesoreria) VALUES ($rowTurnos0[IdCierreTurno], $rowTurnos0[IdCierreCajaTesoreria]);";
    } else {
      $sqlInsert = "UPDATE dbo.Table_1 SET idCierreCajaTesoreria=$rowTurnos0[IdCierreCajaTesoreria] WHERE idCierreTurno=$rowTurnos0[IdCierreTurno];";
    }
  } else {
    // NULL
    fb($rowTurnos0['IdCierreCajaTesoreria']);
    $sqlInsert = "INSERT INTO dbo.Table_1 (idCierreTurno, idCierreCajaTesoreria) VALUES ($rowTurnos0[IdCierreTurno], NULL);";
  }
  //echo $sqlInsert;
  //var_dump($rowTurnos0['IdCierreCajaTesoreria']);
  $stmt2 = odbc_exec2( $mssql, $sqlInsert, __LINE__, __FILE__);
}


$sqlTurnos0 = "SELECT IdCierreTurno, IdCierreCajaTesoreria FROM dbo.CierresTurno WHERE dbo.CierresTurno.IdCierreTurno NOT IN (SELECT idCierreTurno FROM dbo.Table_1);";
$stmt0 = odbc_exec2( $mssql, $sqlTurnos0, __LINE__, __FILE__);
while($rowTurnos0 = odbc_fetch_array($stmt0)){
  if(isset($rowTurnos0['IdCierreCajaTesoreria'])){
    $dmd = odbc_fetch_array(odbc_exec($mssql, "SELECT idCierreCajaTesoreria FROM dbo.Table_1 WHERE IdCierreTurno=$rowTurnos0[IdCierreTurno]"));
    //var_dump($dmd);
    if($dmd == NULL){
      $sqlInsert = "INSERT INTO dbo.Table_1 (idCierreTurno, idCierreCajaTesoreria) VALUES ($rowTurnos0[IdCierreTurno], $rowTurnos0[IdCierreCajaTesoreria]);";
    } else {
      $sqlInsert = "UPDATE dbo.Table_1 SET idCierreCajaTesoreria=$rowTurnos0[IdCierreCajaTesoreria] WHERE idCierreTurno=$rowTurnos0[IdCierreTurno];";
    }
  } else {
    // NULL
    $sqlInsert = "INSERT INTO dbo.Table_1 (idCierreTurno, idCierreCajaTesoreria) VALUES ($rowTurnos0[IdCierreTurno], NULL);";
  }
  //echo $sqlInsert;
  //var_dump($rowTurnos0['IdCierreCajaTesoreria']);
  $stmt2 = odbc_exec2( $mssql, $sqlInsert, __LINE__, __FILE__);
}





if($cierresTurnos==''&&$andFecha==''&&$soloabiertos=='')$top=' TOP 10 ';else $top='';


$sqlTurnos = "SELECT {$top}dbo.CierresTurno.IdCierreTurno, Fecha, IdCaja, IdEmpleado2, IdEmpleado3, IdEmpleado4, EmitioFacturaComplemento, dbo.CierresTurno.IdCierreCajaTesoreria, dbo.Table_1.idCierreCajaTesoreria, dbo.Table_1.lotesRevisados FROM dbo.CierresTurno, dbo.Table_1 WHERE dbo.CierresTurno.IdCierreTurno=dbo.Table_1.idCierreTurno{$cierresTurnos}{$soloabiertos}{$andFecha}{$factdif}{$idCaja}{$soloNoRevisados} ORDER BY dbo.CierresTurno.IdCierreTurno DESC;";

fb($sqlTurnos);

$stmt = odbc_exec2( $mssql, $sqlTurnos, __LINE__, __FILE__);
while($rowTurnos = odbc_fetch_array($stmt)){
  /* print_r($rowTurnos); */
  $fecha = fecha($rowTurnos['Fecha'], 'dmyH');
  
  $caja = ($rowTurnos['IdCaja']==1)?'<span class="label label-warning">PLAYA</span>':(($rowTurnos['IdCaja']==2)?'<span class="label label-info">SHOP</span>':'<span class="label label-info">ADMIN</span>');
  
  $factdiferencia = ($_POST['buscador']=='turnos')?(($rowTurnos['EmitioFacturaComplemento']==1)?'':" alert alert-danger"):'';
  
  
  $debe=$haber=0;
  
  $empleados = '';
  $empleados .= (isset($rowTurnos['IdEmpleado2']))?'+'.$_SESSION['empleados'][$rowTurnos['IdEmpleado2']]:'';
  $empleados .= (isset($rowTurnos['IdEmpleado3']))?' +'.$_SESSION['empleados'][$rowTurnos['IdEmpleado3']]:'';
  $empleados .= (isset($rowTurnos['IdEmpleado4']))?' +'.$_SESSION['empleados'][$rowTurnos['IdEmpleado4']]:'';
  
  if($_POST['buscador']=='tarjetas'){
    $lotesRevisados = "<td class='lotesRevisados'><span class='label label-".(($rowTurnos['lotesRevisados']==1)?"success'>REVISADO":"danger'>NO REVISADO")."</span></td>";
  } else {
    $lotesRevisados = "<td></td>";
  }
  $verLotesTurno = ($_POST['buscador']<>'turnos')?"<td class='verTurno' id='turno_$rowTurnos[IdCierreTurno]'><span class='viendo label label-success'>VER TURNO</span></td>":'';
  if($rowTurnos['IdCierreCajaTesoreria'] == NULL){
    $activaDesactiva = "";
  } else {
    $activaDesactiva = "<span class='abreCierra label label-".(($rowTurnos['IdCierreCajaTesoreria']<>$rowTurnos['idCierreCajaTesoreria'])?"warning'><i class='glyphicon glyphicon-warning-sign'></i>CERRAR":"success'>ABRIR")."</span>";
  }
      
      
  echo "<tbody class='turno$factdiferencia' id='t$rowTurnos[IdCierreTurno]'><tr class='encabezaAsiento'>{$lotesRevisados}<td align='left'>$caja $fecha</td><td colspan='2'>Nº $rowTurnos[IdCierreTurno]</td></tr>";
  
  
  echo "<tr class='fila'><td class='cuentaD' colspan='2'>$empleados</td>$verLotesTurno<td class='debe' id='$rowTurnos[IdCierreTurno]'>$activaDesactiva</td></tr></tbody>";	
}
if(!isset($fecha))echo "<tbody><tr><td colspan='3'>No hay resultados</td></tr></tbody>";
?>
