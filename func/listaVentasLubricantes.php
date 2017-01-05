<?php
// calculaPromedios.php
include_once('../include/inicia.php');
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

$sqlVentas = trim('SELECT MovimientosDetalleFac.IdArticulo, Cantidad, PrecioPublico, MovimientosDetalleFac.IdCierreTurno, Codigo, Descripcion, IdGrupoDescuento, IdEmpleado2, IdEmpleado3, IdEmpleado4, CASE WHEN IdEmpleado3>0 THEN PrecioPublico/2 else PrecioPublico END AS VENTAS, dbo.CierresTurno.Fecha, dbo.MovimientosFac.IdTipoMovimiento, dbo.CierresTurno.IdCierreTurno, DATEPART(hh, dbo.CierresTurno.Fecha) as hora, isnumeric(IdEmpleado2*IdEmpleado3*IdEmpleado4) as turnoTriple FROM dbo.MovimientosDetalleFac, dbo.Articulos, dbo.MovimientosFac, dbo.CierresTurno WHERE dbo.Articulos.IdGrupoArticulo=57 AND dbo.MovimientosDetalleFac.IdArticulo=dbo.Articulos.IdArticulo AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosFac.Fecha>=\''.$desde.'\' AND dbo.MovimientosFac.Fecha<\''.$hasta.'\' AND dbo.CierresTurno.IdCierreTurno=dbo.MovimientosDetalleFac.IdCierreTurno '.$soloElaionGrande.$soloEmpleado.' AND Descripcion like (\'%ELAION%\') AND Descripcion NOT LIKE (\'%MOTO%\') AND Descripcion NOT LIKE (\'%NAUTICO%\') AND (descripcion LIKE (\'%1LT%\') OR descripcion LIKE (\'%1 LT%\')) AND idEmpleado2 NOT IN '.$_SESSION['empleadosZZ'].' AND (idEmpleado3 NOT IN '.$_SESSION['empleadosZZ'].' OR idEmpleado3 is NULL) ORDER BY dbo.CierresTurno.IdCierreTurno, IdEmpleado2, IdEmpleado3, IdEmpleado4,  MovimientosDetalleFac.IdArticulo;');

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

$stmt = sqlsrv_query( $mssql, $sqlVentas);
if( $stmt === false ){
     echo "1. Error in executing query.</br>$sqlVentas<br/>";
     die( print_r( sqlsrv_errors(), true));
}
$totalVendido = $turnoAnterior = $articuloAnterior = $tipoMovimiento = $totalComisiona = $cantidadVendida = 0;
$cantidad = 0;
$turnos = array();
$linea = array();
$a=0;$b=-1;//print_r($_POST);
if($titulo<>"")echo "<tr><td><b>$titulo</b></td></tr>";
while($rowVentas = sqlsrv_fetch_array($stmt)){
    if($rowVentas['turnoTriple']==1){
      // borro a Federico de la ecuacion
      if($rowVentas['IdEmpleado2']==$tercerEmpleado){
        $rowVentas['IdEmpleado2']=$rowVentas['IdEmpleado4'];
      } elseif($rowVentas['IdEmpleado3']==$tercerEmpleado){
        $rowVentas['IdEmpleado3']=$rowVentas['IdEmpleado4'];
      }
    }

    $litros = (strpos($rowVentas['Descripcion'], '1LTS')||strpos($rowVentas['Descripcion'], '1 LTS'))?1:((strpos($rowVentas['Descripcion'], '4LTS')||strpos($rowVentas['Descripcion'], '4 LTS'))?4:'XXX');
    $ponderacion = ($rowVentas['hora']<=7&&isset($_POST['noche']))?$ponderaNoche:1;
    $esNoche = ($rowVentas['hora']<=7&&isset($_POST['noche']))?' noche':'';
    $a++;
    $resta = ($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?' nc':'';
    $resta2 = ($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?'-':'';
    
 
    
    if($turnoAnterior == $rowVentas['IdCierreTurno'] && $articuloAnterior == $rowVentas['Codigo'] && $tipoMovimiento == $rowVentas['IdTipoMovimiento']){
        $cantidadVendida += $cantidad*(($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?-1:1);
        $cantidad = $cantidad + $rowVentas['Cantidad'];
        $linea[$a-1]='';
        $totalComisiona -= $comisiona;
        
    } else {
        $cantidad = intval($rowVentas['Cantidad']);
        $cantidadVendida += $cantidad*(($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?-1:1);
    }
    
    $comisiona = $ponderacion*$cantidad*$rowVentas['VENTAS']*(($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?-1:1);
    
    $totalComisiona += $comisiona;
    
    if($turnoAnterior <> $rowVentas['IdCierreTurno']){
        $cebra = (pow(-1,$b)<0)?" cebra":"";
        $b++;
    }
    
    $turnoAnterior = $rowVentas['IdCierreTurno'];
    $articuloAnterior = $rowVentas['Codigo'];
    $tipoMovimiento = $rowVentas['IdTipoMovimiento'];
    
    //$linea[$a] = "<tr class='fila$resta{$cebra}'><td align=left>(".trim($rowVentas['Codigo']).") {$rowVentas['Descripcion']}</td><td class='debe'>$resta2{$litros} lts / $resta2".sprintf("%.2f",$rowVentas['PrecioPublico'])."</td><td>x $cantidad</td><td class='haber$esNoche'>".(($esNoche<>'')?"Turno Noche x $ponderacion ":'').(($resta2<>'')?'N/C ':''); // incluye litros
    $linea[$a] = "<tr class='fila$resta{$cebra}'><td align=left>(".trim($rowVentas['Codigo']).") {$rowVentas['Descripcion']}</td><td class='debe'>$resta2".sprintf("%.2f",$rowVentas['PrecioPublico'])."</td><td class='haber$esNoche'>".(($esNoche<>'')?"Turno Noche x $ponderacion ":'').(($resta2<>'')?'N/C ':'');
    
    
    
    $linea[$a] .="$resta2".sprintf("%.2f",$ponderacion*$rowVentas['VENTAS'])."</td><td>x $cantidad</td><td class='haber$esNoche'>".sprintf("%.2f", $comisiona)."</td><td class='haber'>{$vendedor[$rowVentas['IdEmpleado2']]}";
    
    $linea[$a] .=((isset($rowVentas['IdEmpleado3'])&&$rowVentas['IdEmpleado3']>0)?' - '.$vendedor[$rowVentas['IdEmpleado3']]:'');
    $linea[$a] .=(($rowVentas['turnoTriple']==1)?' <small><span class="label label-danger">+ Reising</span></small>':'');
    $linea[$a] .="</td><td>".date_format($rowVentas['Fecha'], "d/m/Y H:i")."</td></tr>";
    
    $totalVendido+=$ponderacion*$rowVentas['VENTAS'];
    
    if(!in_array($rowVentas['Fecha'], $turnos)){
        $turnos[]=$rowVentas['Fecha'];
    }
}
foreach($linea as $echo){
    echo $echo;
}
echo "<tr class='fila filaTotal'><td></td><td></td><td>".sprintf("%.2f",$totalVendido)."</td><td>$cantidadVendida</td><td>".sprintf("%.2f",$totalComisiona)."</td><td>En ".count($turnos)." turnos</td></tr>";

?>
