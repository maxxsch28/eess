<?php
// calculaPromedios.php
include_once(($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php'));


if(!isset($_POST['noche']))$ponderaNoche=1;

error_reporting(E_ALL ^ E_NOTICE);
$cuantosMeses=12;
//print_r($_POST);


$hasta = date("Y-m-d", strtotime("+1 month", strtotime($_POST['mes'].'01')));
$inicia = date("Y-m-d", strtotime("+0 month", strtotime($_POST['mes'].'01')));
if($_POST['mes']<>''){
    //echo $inicia;
}
//$articuloVendedor = array();
$totalPorVendedor = array();
$mes = array();
$sumaTotal = $cantidadTurnos = $cantidad = 0;
$meses = array();
$desde = date("Y-m-d", strtotime("-$cuantosMeses month", strtotime($inicia)));

echo "<thead><tr><th></th><th class='ampliar' id='"
        .date("Ym01", strtotime("+0 month", strtotime($_POST['mes'].'01')))
        ."'>"
        .date("M", strtotime("+0 month", strtotime($_POST['mes'].'01')))
        ."</th><th class='columna'>&Delta; pers<sup>1</sup></th><th class='columna'>&Delta; grupal<sup>2</sup></th><th class='comision ni'>$</th><th class='columna'>x&#772;</th>";
for($i = 1; $i < $cuantosMeses+1; $i++){echo "<th class='ampliar' id='".date("Ym01", strtotime("-$i month", strtotime($_POST['mes'].'01')))."'>".date("M", strtotime("-$i month", strtotime($_POST['mes'].'01')))."</th>";}
echo "</tr></thead><tbody align='right'>";
$filtroClientes = " AND IdCliente NOT IN (1043, 1561, 1993, 5233, 5235, 3329, 1737) ";
$filtroClientes = "";
$sqlVentas = trim('SELECT cpe1.IdCierreTurno, cpe1.Fecha, Cpe1.IdEmpleado1, Cpe1.IdEmpleado2, cpe1.IdEmpleado3, cpe2.sumaTurno, DATEPART(YEAR,cpe1.fecha) as anio, DATEPART(MONTH,cpe1.fecha) AS mes FROM dbo.cierresturno cpe1
INNER JOIN
(
        SELECT dbo.movimientosdetallefac.IdCierreturno, SUM(PrecioPublico*Cantidad*(CASE WHEN IdTipoMovimiento IN (\'FAA\',\'FAB\',\'TIK\') THEN 1 ELSE -1 END)) AS sumaTurno FROM dbo.movimientosdetallefac, dbo.movimientosfac, dbo.cierresturno, dbo.Articulos WHERE 
          dbo.movimientosdetallefac.IdCierreTurno=dbo.cierresturno.IdCierreTurno AND 
          dbo.movimientosdetallefac.IdMovimientoFac=dbo.movimientosfac.IdMovimientoFac AND 
          dbo.Articulos.IdArticulo=dbo.MovimientosDetalleFac.IdArticulo AND 
          dbo.cierresturno.IdCaja=2 AND 
          IdGrupoArticulo NOT IN (6, 49, 36, 1, 57, 46, 47, 48, 11, 35, 45, 39, 44, 32, 34, 33, 31, 8, 54, 37, 10) '.$filtroClientes.' AND IdTipoMovimiento NOT IN (\'REM\',\'RDV\') AND dbo.cierresturno.Fecha>=\''.$desde.'\' AND dbo.cierresturno.Fecha<\''.$hasta.'\' group by dbo.movimientosdetallefac.IdCierreTurno
) cpe2
    ON cpe1.IdCierreTurno = cpe2.IdCierreTurno
WHERE cpe1.Fecha>=\''.$desde.'\' AND cpe1.Fecha<\''.$hasta.'\' AND (IdEmpleado2<>33 AND IdEmpleado3<>33) ORDER BY fecha DESC;');

    

// ChromePhp::log($sqlVentas);

$stmt = odbc_exec($mssql, $sqlVentas);
if( $stmt === false ){
      echo "1. Error in executing query.</br>$sqlVentas<br/>";
      die( print_r( sqlsrv_errors(), true));
}
//if(!isset($articulo)){$articulo = array();} // deprecated, esto sirve para el listaVentas

while($rowVentas = odbc_fetch_array($stmt)){
  $mes = $rowVentas['anio'].sprintf('%02d', $rowVentas['mes']);
  $meses[] = $mes;
  /*IdCierreTurno	Fecha	                  IdEmpleado1	IdEmpleado2	IdEmpleado3	sumaTurno
    7688	        2015-11-29 22:49:20.000	   NULL	           15	             29	         3705.00000000*/
  if($rowVentas['IdEmpleado1']>0){
    $cajero[$rowVentas['IdEmpleado1']][$mes][]=$rowVentas['sumaTurno']/2;
    $cajero[$rowVentas['IdEmpleado1']][$mes]['q']++;
    $cajero[$rowVentas['IdEmpleado1']]['cantidadTurnos']++;
    $cajero[$rowVentas['IdEmpleado1']]['nombre']=(isset($empleado[2][$rowVentas['IdEmpleado1']]))?$empleado[2][$rowVentas['IdEmpleado1']]:$empleado[1][$rowVentas['IdEmpleado1']];
  }
  if($rowVentas['IdEmpleado2']>0){
    $cajero[$rowVentas['IdEmpleado2']][$mes][]=$rowVentas['sumaTurno']/2;
    $cajero[$rowVentas['IdEmpleado2']][$mes]['q']++;
    $cajero[$rowVentas['IdEmpleado2']]['cantidadTurnos']++;
    $cajero[$rowVentas['IdEmpleado2']]['nombre']=(isset($empleado[2][$rowVentas['IdEmpleado2']]))?$empleado[2][$rowVentas['IdEmpleado2']]:$empleado[1][$rowVentas['IdEmpleado2']];
  }
  if($rowVentas['IdEmpleado3']>0){
    $cajero[$rowVentas['IdEmpleado3']][$mes][]=$rowVentas['sumaTurno']/2;
    $cajero[$rowVentas['IdEmpleado3']][$mes]['q']++;
    $cajero[$rowVentas['IdEmpleado3']]['cantidadTurnos']++;
    $cajero[$rowVentas['IdEmpleado3']]['nombre']=(isset($empleado[2][$rowVentas['IdEmpleado3']]))?$empleado[2][$rowVentas['IdEmpleado3']]:$empleado[1][$rowVentas['IdEmpleado3']];
  }
  $sumaTotal += $rowVentas['sumaTurno']/2;
  $cantidadTurnos++;
}
$cantidadPeriodos = array();
$sumaEmpleado = array();
foreach($cajero as $idEmpleado => $datos){
  $cantidadPeriodos[$idEmpleado] = 0;
  $sumaEmpleado[$idEmpleado] = 0;
  //ChromePhp::log($datos);
  
  foreach($datos as $mesAnalizado => $facturado){
    if(is_numeric($mesAnalizado)){
      //ChromePhp::log($mesAnalizado);ChromePhp::log($facturado);
      //ChromePhp::log($facturado);//ChromePhp::log( array_sum($facturado));
      $promedioEmpleado[$idEmpleado][$mesAnalizado]=(array_sum($facturado)-$facturado['q'])/$facturado['q'];
      
      if(is_numeric($mesAnalizado)){
        //@$sumaEmpleado[$idEmpleado] = $sumaEmpleado[$idEmpleado] + $facturado;
        $cantidadPeriodos[$idEmpleado]++;
      } else {
        var_dump($mesAnalizado[0]);
      }
      $promedioEmpleadoUltimosMeses[$idEmpleado] = $sumaEmpleado[$idEmpleado] / $cantidadPeriodos[$idEmpleado];
    }
  }
}
ChromePhp::log($promedioEmpleado);


//ChromePhp::log($cajero);
//ChromePhp::log($promedioEmpleadoUltimos12Meses);
//die;
$qq=0;
$total5meses = 0;
$cantidadMesesVendedor = array();
//$ponderaVendedor = array();
foreach($cajero as $id => $quien){
  if(isset($promedioEmpleado[$id])){
    $_SESSION['recibo'][$id][0]=$quien;
    echo "<tr><td align='left'>$quien[nombre]</td>";
    echo "<td class='ampliar2' id='{$id}_".$_POST['mes'].'01'."'>$ ".sprintf("%.2f",$promedioEmpleado[$id][$_POST['mes']])."</td>";
    $totalMes = $totalMes + $promedioEmpleado[$id][$_POST['mes']];
    $linea = "";
    $q=0;
    // Calculo promedio
    for($i = 1; $i < $cuantosMeses+1; $i++){
      
      $mesObservado = date("Ym", strtotime("-$i month", strtotime($_POST['mes'])));
      
      $linea .= "<td class='ampliar2' id='{$id}_".$mesObservado."01'>";
      if($cajero[$id][$mesObservado]>0){
        $cantidadMesesVendedor[$id]++;
        $linea .= "$ ".sprintf("%.2f",$promedioEmpleado[$id][$mesObservado]);
        //ChromePhp::log($cajero[$id]);
        $q++;$qq++;
        $totalMeses = $totalMeses + $promedioEmpleado[$id][$mesObservado];
        $totalEmpleado[$id]=$totalEmpleado[$id]+$promedioEmpleado[$id][$mesObservado];
      } else {
        $linea .= "S/D";
      }
      $linea .="</td>";
    }
    //ChromePhp::log($totalEmpleado);
    $promedio = ($totalEmpleado[$id]>0)?sprintf("%.2f",$totalEmpleado[$id]/$q):'S/D';
    //ChromePhp::log($promedio);
    // Restringe el promedio si el empleado no tiene los 12 meses de antiguedad como los demas
    if($cantidadMesesVendedor[$id]>9){$ponderaVendedor = 1;}
    elseif($cantidadMesesVendedor[$id]>6){$ponderaVendedor = .75;}
    elseif($cantidadMesesVendedor[$id]>3){$ponderaVendedor = .5;}
    else {$ponderaVendedor = .25;}
    
    // Calculo variación sobre promedio
    $variacion = ($promedio>0)?sprintf("%.1f",$ponderaVendedor*(($promedioEmpleado[$id][$_POST['mes']]/$promedio)-1)*100):'';
    $sentido = ($variacion<0)?' class="neg"':'';
    $promedioMensual = $totalMeses/$qq;
    @$variacion2 = sprintf("%.1f",(($cajero[$id][$_POST['mes']]/$promedioMensual)-1)*100);
    $sentido2 = ($variacion2<0)?' class="neg"':'';
    
    @$premio = ceil(($id<>21)?((($variacion>0)?floor($variacion/$comisionPorTantoPorciento)*$comision:0) + (($variacion2>0)?floor($variacion2/$comisionPorTantoPorciento)*$comision:0)):'');
    
    $premioTotal += ($id<>21)?$premio:0;
    
    echo "<td$sentido>$variacion %".(($cantidadMesesVendedor[$id]<12)?"<sup>3</sup>":'')."</td><td$sentido2>$variacion2 %</td><td class='ni'><b>$premio</b></td><td>$ $promedio</td>".$linea;
    
    $_SESSION['recibo'][$id][1]=$premio;
    //$totalMes += $totalPorVendedor[0][$id];
    $qEmpleados++;
    echo "</tr>";
  } else {echo "lola";ChromePhp::log('hola guey');}
}

echo "<tr><td colspan=9>&nbsp;</td></tr>"
  . "<tr><td align='left'>Total</td><td>$ ".sprintf("%.2f",$totalMes)."</td><td></td><td></td><td><b>$premioTotal</b></td><td></td><td colspan='$cuantosMeses' align='center'>Total ".($cuantosMeses-1)." meses anteriores: $ ".sprintf("%.2f",$totalMeses)."</td></tr>";

@$varMensualSobrePromedio = (($totalMes/$promedioMensual)/$qEmpleados)-1;
$sentido3 = ($varMensualSobrePromedio<0)?' class="neg"':'';


echo "<tr><td align='left'></td><td>$ ".sprintf("%.2f",$totalMes/$q)."</td><td$sentido3>".sprintf("%.2f",($varMensualSobrePromedio))." %</td><td></td><td></td><td></td><td colspan=$cuantosMeses align='center'>Promedio ".($cuantosMeses-1)." meses anteriores: <b>$ ".sprintf("%.2f",$promedioMensual).((isset($_POST['noche'])&&!$historicoNoAfectadoNoche)?' <sup>4</sup>':'')."</b></td>";
/*for($i=2;$i<=6;$i++){
    echo "<td class='oculto'>{$mes_{$i}[1]} / {$mes_{$i}[2]} - ".sprintf("%.2f",$mes_{$i}[1] / $mes_{$i}[2])."</td>";
}*/
"</tr></tbody>";
?>
