<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));

unset($_SESSION['litrosMes'], $_SESSION['litrosPrecio']);
//print_r($_REQUEST);

if(!isset($_SESSION['empleados'])){
  $s = "SELECT IdEmpleado, Empleado FROM empleados ORDER BY IdEmpleado";
  $q = odbc_exec2($mssql, $s);
  while($r = odbc_fetch_array($q, SQLSRV_FETCH_ASSOC)){
    $rr[$r['IdEmpleado']]=$r['Empleado'];
  }
  $_SESSION['empleados']=$rr;
}

$andFecha=(isset($_REQUEST['rangoInicio']))?" AND Fecha>='$_REQUEST[rangoInicio]' AND Fecha<='$_REQUEST[rangoFin]'":'';
if(isset($_REQUEST['mes'])&&$_REQUEST['mes']<>''){
  $mesFin= date("Y-m-t", strtotime($_REQUEST['mes']));
  $andFecha=" AND Fecha>='$_REQUEST[mes]' AND Fecha<='$mesFin'";
}

$soloabiertos = (isset($_REQUEST['soloabiertos'])&&$_REQUEST['soloabiertos']==1)?" AND dbo.CierresTurno.IdCierreCajaTesoreria is null":'';
$factdif = (isset($_REQUEST['factdif'])&&$_REQUEST['factdif']==1)?" AND dbo.CierresTurno.EmitioFacturaComplemento=0":'';

$idCaja = (isset($_REQUEST['idCaja'])&&$_REQUEST['idCaja']>0)?" AND IdCaja=$_REQUEST[idCaja]":" AND IdCaja=1";

if(is_array($_REQUEST['idCierreCajaTesoreria'])&&$_REQUEST['idCierreCajaTesoreria'][0]<>''){
  $cierresTurnos = " AND dbo.CierresTurno.IdCierreCajaTesoreria IN (";
  foreach($_REQUEST['idCierreCajaTesoreria'] as $turno){
    $cierresTurnos .= "$turno, ";
  }
  $cierresTurnos = substr($cierresTurnos, 0, -2).")";
} else $cierresTurnos = "";

// levanta datos que faltan en tabla temporal
/*$sqlTurnos0 = "SELECT IdCierreTurno, IdCierreCajaTesoreria FROM dbo.CierresTurno WHERE idCierreTurno NOT IN (SELECT idCierreTurno FROM dbo.Table_1);";
$stmt0 = odbc_exec( $mssql, $sqlTurnos0);
if( $stmt0 === false ){
     echo "1. Error in executing query.</br>$sqlTurnos0<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowTurnos0 = odbc_fetch_array($stmt0)){
	$sqlInsert = "INSERT INTO dbo.Table_1 VALUES ($rowTurnos0[IdCierreTurno], $rowTurnos0[IdCierreCajaTesoreria]);";
	$stmt2 = odbc_exec( $mssql, $sqlInsert);
}*/



if($cierresTurnos==''&&$andFecha==''&&$soloabiertos=='')$top=' TOP 10 ';else $top='';


$sqlTurnos = "select idCierreTurno, Fecha, IdCaja, EmitioFacturaComplemento, IdEmpleado2, IdEmpleado3, IdEmpleado4, IdCierreCajaTesoreria from dbo.CierresTurno WHERE EmitioFacturaComplemento=0 {$idCaja}{$andFecha}{$cierresTurnos}{$soloabiertos}{$factdif} ORDER BY IdCierreTurno ASC;";

//echo $sqlTurnos;

$stmt = odbc_exec2( $mssql, $sqlTurnos, __LINE__, __FILE__);

while($rowTurnos = odbc_fetch_array($stmt)){
  // reviso si el turno pertenece a caja cerrada o no
  $sqlCerrado = "SELECT idCierreCajaTesoreria FROM table_1 WHERE idcierreturno=$rowTurnos[idCierreTurno]";
  //echo $sqlCerrado;
  $stmtCerrado = odbc_exec( $mssql, $sqlCerrado);
  $rowCerrado = odbc_fetch_array($stmtCerrado);
  // levanto los precios historicos del dia
  $anio_mes = fecha($rowTurnos['Fecha'], "dmy","Ym");
  $sqlPreciosHistoricos = "SELECT A.idarticulo, A.fecha, A.precio FROM precioshistoricos AS A INNER JOIN ( SELECT idarticulo, MAX( fecha ) AS fecha FROM precioshistoricos GROUP BY idArticulo ) AS B ON A.idArticulo = B.idArticulo AND A.fecha = B.fecha"; 
  //echo $sqlPreciosHistoricos;
  $precio=array();
  $resPreciosHistoricos = $mysqli->query($sqlPreciosHistoricos);
  if($resPreciosHistoricos&&$resPreciosHistoricos->num_rows>0){
    while($rowPrecio = $resPreciosHistoricos->fetch_array()){
      $precio[$rowPrecio['idarticulo']]=$rowPrecio['precio'];
    }
  }
  foreach($articulo as $key => $idart){
    $litrosFacturados[$key] = 0;
  }
  // saco los litros facturados o remitidos en el turno
  $sqlLitrosFacturados = "select idArticulo, SUM(cantidad) as lts from dbo.MovimientosDetalleFac, dbo.MovimientosFac where MovimientosFac.IdMovimientoFac=MovimientosDetalleFac.IdMovimientoFac and IdCierreTurno=$rowTurnos[idCierreTurno] and IdArticulo in (2068, 2069, 2076, 2078) group by IdArticulo";
  $stmtLitrosFacturados = odbc_exec2( $mssql, $sqlLitrosFacturados, __LINE__, __FILE__);
  while($rowLitrosFacturados = odbc_fetch_array($stmtLitrosFacturados)){
    $litrosFacturados[$rowLitrosFacturados['idArticulo']]=$rowLitrosFacturados['lts'];
  }
  //print_r($litrosFacturados);
  
  /* print_r($rowTurnos); */
  // obtengo el cierre de turno que estoy buscando
  $sqlCierreSurtidores = "select idCierreSurtidores from dbo.CierresSurtidores where IdCierreTurno=$rowTurnos[idCierreTurno]";
  $stmtCierreSurtidores = odbc_exec2( $mssql, $sqlCierreSurtidores, __LINE__, __FILE__);
  $rowCierreSurtidores = odbc_fetch_array($stmtCierreSurtidores);
  
  // saco estado aforadores cierre de turno anterior
  $sqlAforadoresAnteriores = "select AforadorElectronico, CierresDetalleSurtidores.IdManguera from dbo.CierresDetalleSurtidores, dbo.Mangueras where IdCierreSurtidores=".($rowCierreSurtidores['idCierreSurtidores']-1)." and CierresDetalleSurtidores.IdManguera=Mangueras.IdManguera";
  $stmtAforadoresAnteriores = odbc_exec2( $mssql, $sqlAforadoresAnteriores, __LINE__, __FILE__);
  $aforadorAnterior = array();
  while($rowAforadoresAnteriores = odbc_fetch_array($stmtAforadoresAnteriores)){
    $aforadorAnterior[$rowAforadoresAnteriores['IdManguera']]=$rowAforadoresAnteriores['AforadorElectronico'];
  }
  
  // saco lecturas del turno actual
  $sqlAforadoresActuales = "select Descripcion, Importe as ImporteTeorico, ImporteTurno, articulos.PrecioPublico, AforadorElectronico, CierresDetalleSurtidores.IdManguera, dbo.Articulos.IdArticulo from dbo.CierresDetalleSurtidores, dbo.Mangueras, dbo.Articulos where IdCierreSurtidores=$rowCierreSurtidores[idCierreSurtidores] and CierresDetalleSurtidores.IdManguera=Mangueras.IdManguera and dbo.Articulos.IdArticulo=dbo.Mangueras.IdArticulo";
  //echo $sqlAforadoresActuales;
  $stmtAforadoresActuales= odbc_exec2( $mssql, $sqlAforadoresActuales, __LINE__, __FILE__);
  $aforadorActual = array();
  $tr = "";
  $producto = array();
  if(!isset($_SESSION['litros'])){$_SESSION['litros'] = array();}
  while($rowAforadoresActuales = odbc_fetch_array($stmtAforadoresActuales)){
      $aforadorActual[$rowAforadoresActuales['IdManguera']]=$rowAforadoresActuales['AforadorElectronico'];
      if(!isset($producto[$rowAforadoresActuales['IdArticulo']])){$producto[$rowAforadoresActuales['IdArticulo']]=0;}
      $producto[$rowAforadoresActuales['IdArticulo']] += ($rowAforadoresActuales['AforadorElectronico']-$aforadorAnterior[$rowAforadoresActuales['IdManguera']]);
  }
  ksort($producto);
  foreach($producto as $prod => $litros){
      $litros = $litros-$litrosFacturados[$prod];
      if(round($litros,2)<>0){
          $tr.="<tr><td><b>$articulo[$prod]</b></td><td><b>".sprintf("%01.2f",$litros)." lts</b></td><td>".sprintf("%01.2f",$litros*$precio[$prod])."</td><td>".sprintf("%01.2f",$precio[$prod])."</td></tr>";
          if(isset($_SESSION['litrosMes'][$anio_mes])){
              $_SESSION['litrosMes'][$anio_mes]+=$litros;
          } else {
              $_SESSION['litrosMes'][$anio_mes]=$litros;
          }
          if(isset($_SESSION['litrosPrecio'][$prod])){
              $_SESSION['litrosPrecio'][$prod][$precio[$prod]]+=$litros;
          } else {
              $_SESSION['litrosPrecio'][$prod][$precio[$prod]]=$litros;
          }
      }
  }
  
      //$fecha = date_format($rowTurnos['Fecha'], "d/m/Y H:i:s");
      $fecha = $rowTurnos['Fecha'];
      
      //$caja = ($rowTurnos['IdCaja']==1)?'<span class="badge badge-warning">PLAYA</span>':'<span class="badge badge-info">SHOP</span>';
      $caja = "";
      $factdiferencia = ($rowTurnos['EmitioFacturaComplemento']==1)?'':" alert alert-danger";
  if(isset($rowCerrado['idCierreCajaTesoreria'])){
      $activaDesactiva = "<span class='abreCierra label label-".((isset($rowTurnos['idCierreCajaTesoreria'])&&$rowTurnos['IdCierreCajaTesoreria']<>$rowTurnos['idCierreCajaTesoreria'])?"important'><i class='glyphicon glyphicon-warning-sign'></i>CERRAR":"success'>ABRIR")."</span>";
  } else {
      $activaDesactiva = "";
  }
      $empleados = '';
      $empleados .= (isset($rowTurnos['IdEmpleado2']))?'+'.$_SESSION['empleados'][$rowTurnos['IdEmpleado2']]:'';
      $empleados .= (isset($rowTurnos['IdEmpleado3']))?' +'.$_SESSION['empleados'][$rowTurnos['IdEmpleado3']]:'';
      $empleados .= (isset($rowTurnos['IdEmpleado4']))?' +'.$_SESSION['empleados'][$rowTurnos['IdEmpleado4']]:'';
  if($tr<>""){
      echo "<tbody class='turno' id='t$rowTurnos[idCierreTurno]'><tr class='encabezaAsiento'><td align='left' colspan=3>$empleados $caja <b>$fecha</b></td><td>$rowTurnos[idCierreTurno]</td><td class='debe' id='$rowTurnos[idCierreTurno]'>$activaDesactiva</td></tr>";
      
      

      echo "$tr</tbody>";	
  }
}
if(!isset($fecha)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>
