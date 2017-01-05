<?php
// calculaPromedios.php
include_once('../include/inicia.php');


if(!isset($_POST['noche']))$ponderaNoche=1;

error_reporting(E_ALL ^ E_NOTICE);
$limit=11;
$offset=0;
//print_r($_POST);


$hasta = date("Y-m-d", strtotime("+1 month", strtotime($_POST['mes'].'01')));
$inicia = date("Y-m-d", strtotime("+0 month", strtotime($_POST['mes'].'01')));
if($_POST['mes']<>''){
    //echo $inicia;
}
//$articuloVendedor = array();
$totalPorVendedor = array();
$mes = array();


echo "<thead><tr><th></th><th class='ampliar' id='"
        .date("Ym01", strtotime("+0 month", strtotime($_POST['mes'].'01')))
        ."'>"
        .date("M", strtotime("+0 month", strtotime($_POST['mes'].'01')))
        ."</th><th class='columna'>&Delta; pers<sup>1</sup></th><th class='columna'>&Delta; grupal<sup>2</sup></th><th class='comision ni'>$</th><th class='columna'>x&#772;</th>";
for($i = 1; $i < $cuantosMeses+1; $i++){echo "<th class='ampliar' id='".date("Ym01", strtotime("-$i month", strtotime($_POST['mes'].'01')))."'>".date("M", strtotime("-$i month", strtotime($_POST['mes'].'01')))."</th>";}
echo "</tr></thead><tbody align='right'>";



$total = 0;
for($i = 0; $i < $cuantosMeses+1; $i++){
    $desde = date("Y-m-d", strtotime("-$i month", strtotime($inicia)));
    $hasta = date("Y-m-d", strtotime("+1 month", strtotime($desde)));
    $mes = date("m", strtotime("-$i month", strtotime(date("Y-m-01")))); //dbo.MovimientosFac.IdTipoMovimiento<>\'REM\'
    $soloElaionGrande = " AND IdGrupoDescuento>0 ";
    $soloElaionGrande = "";
    $sqlVentas = trim('SELECT MovimientosDetalleFac.IdArticulo, Cantidad, PrecioPublico, MovimientosDetalleFac.IdCierreTurno, Codigo, Descripcion, IdGrupoDescuento, dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3, dbo.CierresTurno.IdEmpleado4, CASE WHEN dbo.CierresTurno.IdEmpleado3>0 THEN PrecioPublico/2 else PrecioPublico END AS VENTAS, dbo.CierresTurno.Fecha, dbo.MovimientosFac.IdTipoMovimiento, DATEPART(hh, dbo.CierresTurno.Fecha) as hora FROM dbo.MovimientosDetalleFac, dbo.Articulos, dbo.MovimientosFac, dbo.CierresTurno WHERE (IdCliente NOT IN (1993)OR IdCliente is null) AND dbo.Articulos.IdGrupoArticulo=57 AND dbo.MovimientosDetalleFac.IdArticulo=dbo.Articulos.IdArticulo AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosFac.Fecha>=\''.$desde.'\' AND dbo.MovimientosFac.Fecha<\''.$hasta.'\' AND dbo.CierresTurno.IdCierreTurno=dbo.MovimientosDetalleFac.IdCierreTurno '.$soloElaionGrande.' AND Descripcion like (\'%ELAION%\') AND Descripcion NOT LIKE (\'%MOTO%\') AND Descripcion NOT LIKE (\'%NAUTICO%\') AND (descripcion LIKE (\'%1LT%\') OR descripcion LIKE (\'%1 LT%\')) AND dbo.CierresTurno.IdCierreTurno<>3227 AND idEmpleado2 NOT IN '.$_SESSION['empleadosZZ'].' AND (idEmpleado3 NOT IN '.$_SESSION['empleadosZZ'].' OR idEmpleado3 IS NULL) ORDER BY dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3;');
    
    
    $sqlVentas = trim('SELECT MovimientosDetalleFac.IdArticulo, Cantidad, PrecioPublico, MovimientosDetalleFac.IdCierreTurno, Codigo, Descripcion, IdGrupoDescuento, dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3, dbo.CierresTurno.IdEmpleado4, PrecioPublico AS VENTAS, dbo.CierresTurno.Fecha, dbo.MovimientosFac.IdTipoMovimiento, DATEPART(hh, dbo.CierresTurno.Fecha) as hora, isnumeric(IdEmpleado2*IdEmpleado3*IdEmpleado4) as turnoTriple FROM dbo.MovimientosDetalleFac, dbo.Articulos, dbo.MovimientosFac, dbo.CierresTurno WHERE (IdCliente NOT IN (1993) OR IdCliente is null) AND dbo.Articulos.IdGrupoArticulo=57 AND dbo.MovimientosDetalleFac.IdArticulo=dbo.Articulos.IdArticulo AND dbo.MovimientosDetalleFac.IdMovimientoFac=dbo.MovimientosFac.IdMovimientoFac AND dbo.MovimientosFac.Fecha>=\''.$desde.'\' AND dbo.MovimientosFac.Fecha<\''.$hasta.'\' AND dbo.CierresTurno.IdCierreTurno=dbo.MovimientosDetalleFac.IdCierreTurno '.$soloElaionGrande.' AND Descripcion like (\'%ELAION%\') AND Descripcion NOT LIKE (\'%MOTO%\') AND Descripcion NOT LIKE (\'%NAUTICO%\') AND (descripcion LIKE (\'%1LT%\') OR descripcion LIKE (\'%1 LT%\')) AND idEmpleado2 NOT IN '.$_SESSION['empleadosZZ'].' AND (idEmpleado3 NOT IN '.$_SESSION['empleadosZZ'].' OR idEmpleado3 IS NULL) AND (idEmpleado4 NOT IN '.$_SESSION['empleadosZZ'].' OR idEmpleado4 IS NULL) ORDER BY dbo.CierresTurno.IdEmpleado2, dbo.CierresTurno.IdEmpleado3, dbo.CierresTurno.IdEmpleado4;'); // Modificacion para corregir Fede 21 julio 2016
    
    fb($sqlVentas);
    

    $stmt = sqlsrv_query($mssql, $sqlVentas);
    if( $stmt === false ){
         echo "1. Error in executing query.</br>$sqlVentas<br/>";
         die( print_r( sqlsrv_errors(), true));
    }
    //if(!isset($articulo)){$articulo = array();} // deprecated, esto sirve para el listaVentas

    while($rowVentas = sqlsrv_fetch_array($stmt)){
      if($rowVentas['turnoTriple']==1){
        // borro a Federico de la ecuacion
        if($rowVentas['IdEmpleado2']==$tercerEmpleado){
          $rowVentas['IdEmpleado2']=$rowVentas['IdEmpleado4'];
          $rowVentas['IdEmplado4']=0;
        } elseif($rowVentas['IdEmpleado3']==$tercerEmpleado){
          $rowVentas['IdEmpleado3']=$rowVentas['IdEmpleado4'];
          $rowVentas['IdEmplado4']=0;
        }
      }
      $ponderacion = ($rowVentas['hora']<=7)?$ponderaNoche:1;
      //if(!isset($articulo[$rowVentas['Codigo']])){$articulo[$rowVentas['Codigo']]=$rowVentas['Descripcion'];} // deprecated, esto sirve para el listaVentas
      $signo=($rowVentas['IdTipoMovimiento']=='NCB'||$rowVentas['IdTipoMovimiento']=='NCA')?-1:1;
      $divide = 1;
      if($rowVentas['IdEmpleado3']>0){
        // dos vendedores en el turno
        $totalPorVendedor[$i][$rowVentas['IdEmpleado3']] += $signo*$ponderacion*$rowVentas['VENTAS']/2*$rowVentas['Cantidad'];
        $totalPorVendedorSinPonderacion[$i][$rowVentas['IdEmpleado3']] += $signo*$rowVentas['VENTAS']/2*$rowVentas['Cantidad'];
        //$articuloVendedor[$i][$rowVentas['IdEmpleado3']][$rowVentas['Codigo']] = $articuloVendedor[$i][$rowVentas['IdEmpleado3']][$rowVentas['Codigo']]+$signo*$rowVentas['Cantidad'];
        $divide = .5;
      } 
      $totalPorVendedor[$i][$rowVentas['IdEmpleado2']] += $signo*$ponderacion*$rowVentas['VENTAS']*$divide*$rowVentas['Cantidad'];
      $totalPorVendedorSinPonderacion[$i][$rowVentas['IdEmpleado2']] += $signo*$rowVentas['VENTAS']*$divide*$rowVentas['Cantidad'];
      //$articuloVendedor[$i][$rowVentas['IdEmpleado2']][$rowVentas['Codigo']] = $articuloVendedor[$i][$rowVentas['IdEmpleado2']][$rowVentas['Codigo']]+$signo*$rowVentas['Cantidad'];
      unset($totalPorVendedor[$i][21], $totalPorVendedorSinPonderacion[$i][21]); // elimino cubrevacaciones
      unset($totalPorVendedor[$i][24], $totalPorVendedorSinPonderacion[$i][24]); // elimino cubrevacaciones
    }
    if($i>0){ // meses desde el actual, no incluído
      $totalVentas               += array_sum($totalPorVendedor[$i]);
      $totalVentasSinPonderacion += array_sum($totalPorVendedorSinPonderacion[$i]);
      $totalVentasMes               += array_sum($totalPorVendedor[$i])/count($totalPorVendedor[$i]);
      $totalVentasMesSinPonderacion += array_sum($totalPorVendedorSinPonderacion[$i])/count($totalPorVendedorSinPonderacion[$i]);
    }
    $mes_{$i}[1] = array_sum($totalPorVendedor[$i]);
    $mes_{$i}[2] = count($totalPorVendedor[$i]);
    $mesSinPonderacion_{$i}[1] = array_sum($totalPorVendedorSinPonderacion[$i]);
    $mesSinPonderacion_{$i}[2] = count($totalPorVendedorSinPonderacion[$i]);
}


$promedioMensual = $totalVentasMes / $cuantosMeses;
$promedioMensualSinPonderacion = $totalVentasMesSinPonderacion / $cuantosMeses;

$totalMes = $promedioMes = $qEmpleados = $premioTotal = 0;

if(!isset($_POST['sinNoche'])){
    $promedioMensual = (!$historicoNoAfectadoNoche)?$promedioMensualSinPonderacion:$promedioMensual;
}

$total5meses = 0;
$cantidadMesesVendedor = array();
//$ponderaVendedor = array();
foreach($vendedor as $id => $quien){
    if(isset($totalPorVendedor[0][$id])){
        $_SESSION['recibo'][$id][0]=$quien;
        echo "<tr><td align='left'>$quien</td>";
        echo "<td class='ampliar2' id='{$id}_".$_POST['mes'].'01'."'>$ ".sprintf("%.2f",$totalPorVendedor[0][$id])."</td>";
        $linea = "";
        $q=0;
        $qq=0;
        // Calculo promedio
        for($i = 1; $i < $cuantosMeses+1; $i++){
            $linea .= "<td class='ampliar2' id='{$id}_".date("Ym01", strtotime("-$i month", strtotime($_POST['mes'].'01')))."'>";
            if($totalPorVendedorSinPonderacion[$i][$id]>0){
                $cantidadMesesVendedor[$id]++;
                //$linea .= "$ ".sprintf("%.2f",$totalPorVendedorSinPonderacion[$i][$id]);
                $linea .= "$ ".sprintf("%.2f",$totalPorVendedor[$i][$id]);
                $q++;
                $qq             += (!$historicoNoAfectadoNoche)?$totalPorVendedorSinPonderacion[$i][$id]:$totalPorVendedor[$i][$id];
                $total5meses    += (!$historicoNoAfectadoNoche)?$totalPorVendedorSinPonderacion[$i][$id]:$totalPorVendedor[$i][$id];
            }
            $linea .="</td>";
        }
        $promedio = ($qq>0)?sprintf("%.2f",$qq/$q):'N/D';// echo "$qq / $q";
        
        // Restringe el promedio si el empleado no tiene los 12 meses de antiguedad como los demas
        if($cantidadMesesVendedor[$id]>9){$ponderaVendedor = 1;}
        elseif($cantidadMesesVendedor[$id]>6){$ponderaVendedor = .75;}
        elseif($cantidadMesesVendedor[$id]>3){$ponderaVendedor = .5;}
        else {$ponderaVendedor = .25;}
        
        // Calculo variación sobre promedio
        $variacion = ($promedio>0)?sprintf("%.1f",$ponderaVendedor*(($totalPorVendedor[0][$id]/$promedio)-1)*100):'';
        $sentido = ($variacion<0)?' class="neg"':'';
        
        $variacion2 = sprintf("%.1f",(($totalPorVendedor[0][$id]/$promedioMensual)-1)*100);
        $sentido2 = ($variacion2<0)?' class="neg"':'';
        
        $premio = ceil(($id<>21)?((($variacion>0)?floor($variacion/$comisionPorTantoPorciento)*$comision:0) + (($variacion2>0)?floor($variacion2/$comisionPorTantoPorciento)*$comision:0)):'');
        
        $premioTotal += ($id<>21)?$premio:0;
        
        echo "<td$sentido>$variacion %".(($cantidadMesesVendedor[$id]<12)?"<sup>3</sup>":'')."</td><td$sentido2>$variacion2 %</td><td class='ni'><b>$premio</b></td><td>$ $promedio</td>".$linea;
        
        $_SESSION['recibo'][$id][1]=$premio;
        $totalMes += $totalPorVendedor[0][$id];
        //$totalMes = $qq;
        $promedioMes += $promedio;
        $qEmpleados++;
        echo "</tr>";
    }
}

echo "<tr><td colspan=9>&nbsp;</td></tr>"
. "<tr><td align='left'>Total</td><td>$ ".sprintf("%.2f",$totalMes)."</td><td></td><td></td><td><b>$premioTotal</b></td><td></td><td colspan='$cuantosMeses' align='center'>Total últimos $cuantosMeses meses: $ ".sprintf("%.2f",$totalVentasSinPonderacion)."  $total</td></tr>";

$varMensualSobrePromedio = (($totalMes/$promedioMensual)/$qEmpleados)-1;
$sentido3 = ($varMensualSobrePromedio<0)?' class="neg"':'';


echo "<tr><td align='left'></td><td>$ ".sprintf("%.2f",$totalMes/$qEmpleados)."</td><td$sentido3>".sprintf("%.2f",($varMensualSobrePromedio))." %</td><td></td><td></td><td></td><td colspan=$cuantosMeses align='center'>Promedio últimos $cuantosMeses meses: <b>$ ".sprintf("%.2f",$promedioMensual).((isset($_POST['noche'])&&!$historicoNoAfectadoNoche)?' <sup>4</sup>':'')."</b></td>";
/*for($i=2;$i<=6;$i++){
    echo "<td class='oculto'>{$mes_{$i}[1]} / {$mes_{$i}[2]} - ".sprintf("%.2f",$mes_{$i}[1] / $mes_{$i}[2])."</td>";
}*/
"</tr></tbody>";
?>
