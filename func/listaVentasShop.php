<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));
if(isset($_POST['desde2'])){
    $array = explode("_" , $_POST['desde2']);
    $desde = $array[1];
    $soloEmpleado = " AND (IdEmpleado2=$array[0] OR IdEmpleado3=$array[0])";
    $titulo = "Mostrando solo {$vendedor[$array[0]]} para el mes ".date("m/Y",strtotime($desde));
}
else {
    
    $desde = $_POST['desde'];
    $soloEmpleado = '';
    $titulo = "Ventas mes ".date("m/Y",strtotime($desde));
}
if(!isset($_POST['noche']))$ponderaNoche=1;

$hasta = date("Y-m-d", strtotime("+1 month", strtotime($desde)));

$soloElaionGrande = " AND IdGrupoDescuento>0";
$soloElaionGrande = "";

$sqlVentas = trim("SELECT cpe1.IdCierreTurno, cpe1.Fecha, Cpe1.IdEmpleado1, Cpe1.IdEmpleado2, cpe1.IdEmpleado3, cpe2.sumaTurno, DATEPART(YEAR,cpe1.fecha) as anio, DATEPART(MONTH,cpe1.fecha) AS mes FROM dbo.cierresturno cpe1
INNER JOIN
(
	SELECT dbo.movimientosdetallefac.IdCierreturno, SUM(PrecioPublico*Cantidad*(CASE WHEN IdTipoMovimiento IN ('FAA','FAB','TIK') THEN 1 ELSE -1 END)) AS sumaTurno FROM dbo.movimientosdetallefac, dbo.movimientosfac, dbo.cierresturno, dbo.Articulos WHERE 
	  dbo.movimientosdetallefac.IdCierreTurno=dbo.cierresturno.IdCierreTurno AND 
	  dbo.movimientosdetallefac.IdMovimientoFac=dbo.movimientosfac.IdMovimientoFac AND 
	  dbo.Articulos.IdArticulo=dbo.MovimientosDetalleFac.IdArticulo AND 
	  dbo.cierresturno.IdCaja=2 AND 
	  IdGrupoArticulo NOT IN (6, 49, 36, 1, 57, 46, 47, 48, 11, 35, 45, 39, 44, 32, 34, 33, 31, 8, 54, 37, 10) AND 
	  dbo.cierresturno.Fecha>'2015-11-23' group by dbo.movimientosdetallefac.IdCierreTurno
) cpe2
    ON cpe1.IdCierreTurno = cpe2.IdCierreTurno
WHERE cpe1.Fecha>='2015-11-23' ORDER BY fecha DESC;");


/*
 SELECT MovimientosDetalleFac.IdArticulo, Cantidad, PrecioPublico, MovimientosDetalleFac.IdCierreTurno, Codigo, Descripcion, IdGrupoDescuento, dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3, dbo.CierresTurno.IdEmpleado4, PrecioPublico AS VENTAS, dbo.CierresTurno.Fecha, dbo.MovimientosFac.IdTipoMovimiento, DATEPART(hh, dbo.CierresTurno.Fecha) as hora, isnumeric(IdEmpleado2*IdEmpleado3*IdEmpleado4) as turnoTriple
 FROM dbo.MovimientosDetalleFac, dbo.Articulos, dbo.MovimientosFac, dbo.CierresTurno 
 WHERE 
	dbo.Articulos.IdGrupoArticulo=57 
	AND dbo.MovimientosDetalleFac.IdArticulo=dbo.Articulos.IdArticulo 
	AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac 
	AND dbo.MovimientosFac.Fecha>='2016-07-01' 
	AND dbo.MovimientosFac.Fecha<'2016-08-01' 
	AND dbo.CierresTurno.IdCierreTurno=dbo.MovimientosDetalleFac.IdCierreTurno 
	AND Descripcion like ('%ELAION%') 
	AND Descripcion NOT LIKE ('%MOTO%')
	AND Descripcion NOT LIKE ('%NAUTICO%')
	AND (descripcion LIKE ('%1LT%') OR descripcion LIKE ('%1 LT%'))  
 ORDER BY dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3, dbo.CierresTurno.IdEmpleado4;
*/

//echo $sqlVentas;

$stmt = odbc_exec( $mssql, $sqlVentas);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlVentas<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$totalVendido = $turnoAnterior = $articuloAnterior = $tipoMovimiento = $sumaTotal = $cantidadTurnos = 0;
$cantidad = 0;
$meses = array();
$linea = array();
$a=0;$b=-1;//print_r($_POST);
if($titulo<>"")echo "<tr><td><b>$titulo</b></td></tr>";
while($rowVentas = odbc_fetch_array($stmt)){
  $mes = $rowVentas['anio'].$rowVentas['mes'];
  $meses[] = $mes;
  /*IdCierreTurno	Fecha	                  IdEmpleado1	IdEmpleado2	IdEmpleado3	sumaTurno
    7688	        2015-11-29 22:49:20.000	   NULL	           15	             29	         3705.00000000*/
  if($rowVentas['IdEmpleado1']>0){
    $empleado[$rowVentas['IdEmpleado1']][$mes]]+=$rowVentas['sumaTurno']/2;
    $empleado[$rowVentas['IdEmpleado1']]['cantidadTurnos']++;
  }
  if($rowVentas['IdEmpleado2']>0){
    $empleado[$rowVentas['IdEmpleado3']][$mes]]+=$rowVentas['sumaTurno']/2;
    $empleado[$rowVentas['IdEmpleado2']]['cantidadTurnos']++;
  }
  if($rowVentas['IdEmpleado3']>0){
    $empleado[$rowVentas['IdEmpleado3']][$mes]]+=$rowVentas['sumaTurno']/2;
    $empleado[$rowVentas['IdEmpleado3']]['cantidadTurnos']++;
  }
  $sumaTotal += $rowVentas['sumaTurno']/2;
  $cantidadTurnos++;
  
}

fb($empleado);
die;

foreach($meses as $mes){
  foreach($empleado as $idEmplado){
    
  }
}






foreach($linea as $echo){
    echo $echo;
}
echo "<tr class='fila filaTotal'><td></td><td></td><td>".sprintf("%.2f",$totalVendido)."</td><td>$cantidadVendida</td><td>".sprintf("%.2f",$totalComisiona)."</td><td>En ".count($turnos)." turnos</td></tr>";

?>
