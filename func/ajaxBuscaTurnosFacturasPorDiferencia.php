<?php
// calculaPromedios.php
include_once('../include/inicia.php');

$limit=11;
$offset=0;
unset($_SESSION['litrosMes'], $_SESSION['litrosPrecio']);
//print_r($_REQUEST);

if(!isset($_SESSION['empleados'])){
	$s = "SELECT IdEmpleado, Empleado FROM empleados ORDER BY IdEmpleado";
	$q = sqlsrv_query($mssql, $s);
	while($r = sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC)){
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
$stmt0 = sqlsrv_query( $mssql, $sqlTurnos0);
if( $stmt0 === false ){
     echo "1. Error in executing query.</br>$sqlTurnos0<br/>";
     die( print_r( sqlsrv_errors(), true));
}
while($rowTurnos0 = sqlsrv_fetch_array($stmt0)){
	$sqlInsert = "INSERT INTO dbo.Table_1 VALUES ($rowTurnos0[IdCierreTurno], $rowTurnos0[IdCierreCajaTesoreria]);";
	$stmt2 = sqlsrv_query( $mssql, $sqlInsert);
}*/



if($cierresTurnos==''&&$andFecha==''&&$soloabiertos=='')$top=' TOP 10 ';else $top='';


$sqlTurnos = "select idCierreTurno, Fecha, IdCaja, EmitioFacturaComplemento, IdEmpleado2, IdEmpleado3, IdEmpleado4, IdCierreCajaTesoreria from dbo.CierresTurno WHERE EmitioFacturaComplemento=0 {$idCaja}{$andFecha}{$cierresTurnos}{$soloabiertos}{$factdif} ORDER BY IdCierreTurno ASC;";

//echo $sqlTurnos;

$stmt = sqlsrv_query( $mssql, $sqlTurnos);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlTurnos<br/>";
     die( print_r( sqlsrv_errors(), true));
}

while($rowTurnos = sqlsrv_fetch_array($stmt)){
    // reviso si el turno pertenece a caja cerrada o no
    $sqlCerrado = "SELECT idCierreCajaTesoreria FROM table_1 WHERE idcierreturno=$rowTurnos[idCierreTurno]";
    //echo $sqlCerrado;
    $stmtCerrado = sqlsrv_query( $mssql, $sqlCerrado);
    $rowCerrado = sqlsrv_fetch_array($stmtCerrado);
    // levanto los precios historicos del dia
    $anio_mes = date_format($rowTurnos['Fecha'], "Ym");
    $sqlPreciosHistoricos = "SELECT A.idarticulo, A.fecha, A.precio FROM precioshistoricos AS A INNER JOIN ( SELECT idarticulo, MAX( fecha ) AS fecha FROM precioshistoricos GROUP BY idArticulo ) AS B ON A.idArticulo = B.idArticulo AND A.fecha = B.fecha"; 
    //echo $sqlPreciosHistoricos;
    $precio=array();
    $resPreciosHistoricos = $mysqli->query($sqlPreciosHistoricos);
    if($resPreciosHistoricos&&$resPreciosHistoricos->num_rows>0){
    while($rowPrecio = $resPreciosHistoricos->fetch_array()){
        $precio[$rowPrecio['idarticulo']]=$rowPrecio['precio'];
    }}
    foreach($articulo as $key => $idart){
        $litrosFacturados[$key] = 0;
    }
    // saco los litros facturados o remitidos en el turno
    $sqlLitrosFacturados = "select idArticulo, SUM(cantidad) as lts from dbo.MovimientosDetalleFac, dbo.MovimientosFac where MovimientosFac.IdMovimientoFac=MovimientosDetalleFac.IdMovimientoFac and IdCierreTurno=$rowTurnos[idCierreTurno] and IdArticulo in (2068, 2069, 2076, 2078) group by IdArticulo";
    $stmtLitrosFacturados = sqlsrv_query( $mssql, $sqlLitrosFacturados);
    while($rowLitrosFacturados = sqlsrv_fetch_array($stmtLitrosFacturados)){
        $litrosFacturados[$rowLitrosFacturados[0]]=$rowLitrosFacturados[1];
    }
    //print_r($litrosFacturados);
    
/* print_r($rowTurnos); */
    // obtengo el cierre de turno que estoy buscando
    $sqlCierreSurtidores = "select idCierreSurtidores from dbo.CierresSurtidores where IdCierreTurno=$rowTurnos[idCierreTurno]";
    $stmtCierreSurtidores = sqlsrv_query( $mssql, $sqlCierreSurtidores);
    $rowCierreSurtidores = sqlsrv_fetch_array($stmtCierreSurtidores);
    
    // saco estado aforadores cierre de turno anterior
    $sqlAforadoresAnteriores = "select AforadorElectronico, CierresDetalleSurtidores.IdManguera from dbo.CierresDetalleSurtidores, dbo.Mangueras where IdCierreSurtidores=".($rowCierreSurtidores[0]-1)." and CierresDetalleSurtidores.IdManguera=Mangueras.IdManguera";
    $stmtAforadoresAnteriores = sqlsrv_query( $mssql, $sqlAforadoresAnteriores);
    $aforadorAnterior = array();
    while($rowAforadoresAnteriores = sqlsrv_fetch_array($stmtAforadoresAnteriores)){
        $aforadorAnterior[$rowAforadoresAnteriores['IdManguera']]=$rowAforadoresAnteriores['AforadorElectronico'];
    }
    
    // saco lecturas del turno actual
    $sqlAforadoresActuales = "select Descripcion, Importe as ImporteTeorico, ImporteTurno, articulos.PrecioPublico, AforadorElectronico, CierresDetalleSurtidores.IdManguera, dbo.Articulos.IdArticulo from dbo.CierresDetalleSurtidores, dbo.Mangueras, dbo.Articulos where IdCierreSurtidores=$rowCierreSurtidores[0] and CierresDetalleSurtidores.IdManguera=Mangueras.IdManguera and dbo.Articulos.IdArticulo=dbo.Mangueras.IdArticulo";
    //echo $sqlAforadoresActuales;
    $stmtAforadoresActuales= sqlsrv_query( $mssql, $sqlAforadoresActuales);
    $aforadorActual = array();
    $tr = "";
    $producto = array();
    if(!isset($_SESSION['litros'])){$_SESSION['litros'] = array();}
    while($rowAforadoresActuales = sqlsrv_fetch_array($stmtAforadoresActuales)){
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
    
	$fecha = date_format($rowTurnos['Fecha'], "d/m/Y H:i:s");
	
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
	echo "<tbody class='turno' id='t$rowTurnos[0]'><tr class='encabezaAsiento'><td align='left' colspan=3>$empleados $caja <b>$fecha</b></td><td>$rowTurnos[0]</td><td class='debe' id='$rowTurnos[0]'>$activaDesactiva</td></tr>";
	
	

	echo "$tr</tbody>";	
    }
}
if(!isset($fecha)){echo "<tbody><tr><td colspan='4'>No hay resultados</td></tr></tbody>";}
?>