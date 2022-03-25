<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$desde = (isset($_POST['desde']))?$_POST['desde']:date('Y-m-d');
$hasta = (isset($_POST['hasta']))?$_POST['hasta']:date("Y-m-d",strtotime('tomorrow'));

 
$tanque = tanques();


// tengo que calcular despachos acumulados por hora y dar un porcentaje de venta muestraDetallesVentasDiarias
// entonces para estimar el total diario tendría que dividir el acumulado de litros del día por este calculado
if(!isset($_SESSION['acumuladoParaEstimacion'][date('w')])){
  unset($_SESSION['acumuladoParaEstimacion']);
  $sql = "select datepart(hour, fecha) as hora, sum(cantidad) as suma, avg(cantidad) as promedio from dbo.despachos where datepart(weekday, Fecha)=datepart(weekday, getdate()) AND fecha>='2018-01-01' GROUP BY datepart(hour, fecha) ORDER BY hora ASC;";
  $totalLitrosDiarios=0;
  $stmt = odbc_exec2($mssql, $sql);
  while($fila = sqlsrv_fetch_array($stmt)){
    $totalLitrosDiarios += $fila['suma'];
    $acumulaHora[$fila['hora']] = $totalLitrosDiarios;
  }
  foreach($acumulaHora as $hora => $litros){
    $_SESSION['acumuladoParaEstimacion'][date('w')][$hora] = $litros/$totalLitrosDiarios;
  }
}
//ChromePhp::log($_SESSION['acumuladoParaEstimacion']);

$pico = array();
// obtengo datos de tanques de CaldenOil
$sqlTanques = "SELECT IdManguera, IdArticulo, a.IdSurtidor, IdTanque, 0 as litros, 0 as importe, 0 as q FROM dbo.mangueras a, dbo.surtidores b WHERE a.IdSurtidor=b.IdSurtidor AND b.IdControladorSurtidores IS NOT NULL UNION select IdManguera, IdArticulo,0,0, SUM(Cantidad) as cantidad, SUM(importe) as importe, COUNT(Cantidad) as q FROM dbo.Despachos where fecha>='$desde' AND fecha<'$hasta' group by IdManguera, IdArticulo ORDER BY IdManguera ASC;";
$stmt = odbc_exec2($mssql, $sqlTanques);
while($fila = sqlsrv_fetch_array($stmt)){
  if($fila['IdTanque']>0){
    $pico[$fila['IdManguera']]['idArticulo']=$fila['IdArticulo'];
    $pico[$fila['IdManguera']]['surtidor']=$fila['IdSurtidor'];
    $pico[$fila['IdManguera']]['tanque']=$fila['IdTanque'];
  } else {
    $pico[$fila['IdManguera']]['litrosDiario']=$fila['litros'];
    $pico[$fila['IdManguera']]['importeDiario']=$fila['importe'];
    $pico[$fila['IdManguera']]['qDespachos']=$fila['q'];
    // en el mismo momento que saco información por picos ya agrupo por tanques
    @$articulo[$fila['IdArticulo']]['litrosDiario'] += $fila['litros'];
    @$articulo[$fila['IdArticulo']]['qDespachos'] += $fila['q'];
  }
}

       
function muestraDetallesVentasDiarias(){
    global $mssql, $articulo, $classArticulo, $tanque, $pico, $desde, $hasta;
    $tablaTanques="";
    // agrupar por producto, para cada producto debo sumar la capacidad de tanques, los litros en cada uno y los despachos.
    
    foreach($tanque as $IdTanque => $datosTanque){
      @$capacidad[$datosTanque['idArticulo']] += $datosTanque['capacidad'];
      @$litros[$datosTanque['idArticulo']] += $datosTanque['litros'];
      $ocupacion[$datosTanque['idArticulo']] = $litros[$datosTanque['idArticulo']]  / $capacidad[$datosTanque['idArticulo']];
      @$nivelPedir[$datosTanque['idArticulo']] += $datosTanque['nivelPedir'];
      @$nivelSuspender[$datosTanque['idArticulo']] += $datosTanque['nivelSuspender'];
    }

    foreach($articulo as $IdArticulo => $datosArticulo){
      /*
      Color: -8323073
​​      descripcion: "NAFTA INFINIA"
​​      familia: "COMBUSTIBLES NAFTAS"
​​      idArticulo: 2076
      */
      // para cada articulo escaneo los picos que correspondan y lo agrupo
      
      $porcentajeOcupacion = $ocupacion[$IdArticulo] * 100;
      $classNoVentas = (($litros[$IdArticulo]-1500)<=$nivelSuspender[$IdArticulo])?'noVentas':"";
      if($litros[$IdArticulo]<=$nivelPedir[$IdArticulo]){$classAlerta='progress-bar-danger';}
      elseif($porcentajeOcupacion<50){$classAlerta='progress-bar-warning';}
      elseif($porcentajeOcupacion<75){$classAlerta='progress-bar-info';}
      else {$classAlerta='progress-bar-success';}
      
      $tablaTanques.="<tr>"
        . "<td class='alert alert-{$classArticulo[$IdArticulo]}'>".$articulo[$IdArticulo]['descripcion']."<br/>$capacidad[$IdArticulo] lts</td>"
        
      ."<td id='ud_$IdArticulo' class='ud $classNoVentas'>".sprintf('%01.2f',$datosArticulo['litrosDiario'])."<br/>".(($datosArticulo['qDespachos']>0)?"$datosArticulo[qDespachos] d. / ".sprintf('%01.1f',$datosArticulo['litrosDiario']/$datosArticulo['qDespachos'])." lts":"----")."</td>"
      
      ."<td>".round($litros[$IdArticulo],0)."<br/>( d.)</td>"
      
      ."<td colspan='2'><div class='progress' style='margin-bottom: 0;'><div class='progress-bar $classAlerta  progress-bar-striped active' role='progressbar' aria-valuenow='$porcentajeOcupacion' aria-valuemin='0' aria-valuemax='100' style='width: $porcentajeOcupacion%;'>".round($porcentajeOcupacion,0)."%</div>".round($capacidad[$IdArticulo]-$litros[$IdArticulo],0)."</div></td>"
      
      //. "<td><span class='$classAlerta'>".round($porcentajeOcupacion,0)."%</span></td>"
      . "</tr>";
      @$totalLitros += $datosArticulo['litrosDiario'];
      @$totalDespachos += $datosArticulo['qDespachos'];
      @$totalFamilia[$datosArticulo['familia']] += $datosArticulo['litrosDiario'];
      if($datosArticulo['premium']){
        $litrosPremium[$datosArticulo['familia']] = $datosArticulo['litrosDiario'];
      }
      //$articulo[$fila['IdArticulo']]['premium']=true;
    }
    // para corregir error después de las 0 de cada día mientras no haya despachos.
    @$totalDiario['d'] = ($totalDiario['d']<>0)?$totalDiario['d']:.0001;
    $mix = "";
    foreach($litrosPremium as $familia => $litros) {
      $mix .= "$familia ".round($litros/$totalFamilia[$familia]*100,0)."% |";
    }
    $mix = substr($mix,0,-2);
    $litrosEstimados = round($totalLitros/$_SESSION['acumuladoParaEstimacion'][date('w')][date("G")]);
     /*echo "$totalLitros/".date('w').'-'.date('G').'-'.$_SESSION['acumuladoParaEstimacion'][date('w')][date("G")];
     print_r($_SESSION['acumuladoParaEstimacion']);
     die;*/
    $tablaTanques .= "<tr class='well'><td width='22%'>Total</td><td>".sprintf('%01.2f',$totalLitros)."<br>$totalDespachos desp / ".round($totalLitros/$totalDespachos,1)." lts</td><td colspan=3>Estimado $litrosEstimados lts<br/>Mix $mix</td></tr>"; 
    
    // Graba estimación para poder ver a lo largo del día como aproxima al valor final real
    $sqlEstimado = "INSERT INTO [coop].[dbo].[estimaciones] (litros) VALUES ('$litrosEstimados');";
    $stmt = odbc_exec2($mssql, $sqlEstimado, __LINE__, __FILE__);
    echo trim($tablaTanques);
    //echo microtime()-$a;
}

function muestraDetallesTanquesTelemedidos(){
  global $mssql, $articulo, $classArticulo, $tanque, $pico, $desde, $hasta;
  $tablaTanques="";
  
  foreach($tanque as $IdTanque => $datosTanque){
    $porcentajeOcupacion = $datosTanque['ocupacion'] * 100;
    $classNoVentas = ($datosTanque['litros']<=$datosTanque['nivelSuspender'])?' class="noVentas"':'';
    if($datosTanque['litros']<=$datosTanque['nivelPedir']){$classAlerta='progress-bar-danger';}
    elseif($porcentajeOcupacion<50){$classAlerta='progress-bar-warning';}
    elseif($porcentajeOcupacion<75){$classAlerta='progress-bar-info';}
    else {$classAlerta='progress-bar-success';}
    
    $tablaTanques.="<tr$classNoVentas>"
      . "<td class='alert alert-{$classArticulo[$datosTanque['idArticulo']]}'>".ucwords(strtolower($articulo[$datosTanque['idArticulo']]['descripcion']))." <span class='badge '>$IdTanque</span></td>"
    ."<td>".sprintf('%01.2f',$datosTanque['litros'])."</td>"
    ."<td colspan='2'><div class='progress' style='margin-bottom: 0;'><div class='progress-bar $classAlerta  progress-bar-striped active' role='progressbar' aria-valuenow='$porcentajeOcupacion' aria-valuemin='0' aria-valuemax='100' style='width: $porcentajeOcupacion%;'>".round($porcentajeOcupacion,0)."%</div>".round($datosTanque['disponible'],0)."</div></td>"
    //. "<td><span class='$classAlerta'>".round($porcentajeOcupacion,0)."%</span></td>"
    . "</tr>";
  }
  echo trim($tablaTanques);
}




function muestraDetalleDescuentosAPP(){
  global $mssql, $articulo, $classArticulo, $tanque, $pico, $desde, $hasta;
  $tablaDescuentos="";
  $totalDescuentosNoRegistrados=0;
  $limiteTextoMovil = 15;
  
  $sqlDescuentos = "select a.idmovimientofac, a.RazonSocial, a.puntoventa, a.numero, a.fecha, a.total as facturado, c.Importe as cobrado, round(a.total-c.importe, 0) as descuento, round(a.NetoConceptosFinancieros*-1.21,0) as descuentoAPP, '' as turno from dbo.movimientosfac a, dbo.MovimientosDetalleFac b, dbo.CuponesTarjetasCredito c where a.IdMovimientoFac=b.IdMovimientoFac and c.IdMovimientoFac=a.IdMovimientoFac AND c.UserName='Pago electronico' AND a.Fecha>='".date('Y-m-d')."' AND b.IdCierreTurno=0 AND b.idarticulo <> '3540' UNION ALL select a.idmovimientofac, a.RazonSocial, a.puntoventa, a.numero, a.fecha, a.total as facturado, c.Importe as cobrado, round(a.total-c.importe, 0) as descuento, round(a.NetoConceptosFinancieros*-1.21,0) as descuentoAPP, d.fecha as turno from dbo.movimientosfac a, dbo.MovimientosDetalleFac b, dbo.CuponesTarjetasCredito c, dbo.CierresTurno d where a.IdMovimientoFac=b.IdMovimientoFac and c.IdMovimientoFac=a.IdMovimientoFac AND c.UserName='Pago electronico' AND a.Fecha>='".date('Y-m-d')."' AND b.IdCierreTurno=d.IdCierreTurno AND d.IdCierreTurno>0 AND b.idarticulo <> '3540' order by turno, a.idmovimientofac desc";
  
  //ChromePhp::log($sqlDescuentos);
  
  $stmt = odbc_exec2($mssql, $sqlDescuentos, __LINE__, __FILE__);
  
  while($fila = sqlsrv_fetch_array($stmt)){

    if($_SESSION['esMovil']){
      $numero = "$fila[PuntoVenta]";
      $limiteTextoMovil = 15;
      $colspan=4;
      $tdTicket = "";

    } else {
      $numero = "$fila[PuntoVenta]-$fila[Numero]";
      $limiteTextoMovil = 18;
      $colspan = 5;
      $tdTicket = "<td>$fila[puntoventa]-$fila[numero]</td>";
    }

    if(round($fila['descuento'],0)>0 || round($fila['descuentoAPP'],0)>0){
      /*
      // desactivado. Esto era para poder separar por turno para los chicos, pero no se utiliza
      if(!isset($turno)) $turno = $fila['turno']->format('H:i');
      if($turno <> $fila['turno']->format('H:i')){
        $tablaDescuentos .= "<tr><td colspan=4><b>A declarar como DESCUENTO SERVICLUB APP YPF</b></td><td>$".round($totalDescuentosNoRegistrados,2)."</td></tr>";
        $totalDescuentosNoRegistrados=0;
      }
      */
      $nombreCliente = ucwords(strtolower((strlen($fila['RazonSocial'])>$limiteTextoMovil)?substr($fila['RazonSocial'],0,$limiteTextoMovil).'...':$fila['RazonSocial']));
      $nombreCliente = ($fila['RazonSocial']=='Consumidor Final')?"Cons. Final":$nombreCliente;
      $totalDescuentosNoRegistrados += $fila['descuento'];
      $tablaDescuentos .= "<tr><td>$nombreCliente</td>$tdTicket<td>$".round($fila['facturado'],2)."</td><td>$".round($fila['descuentoAPP'],2)."</td><td>$".round($fila['descuento'],2)."</td><td>".$fila['turno']->format('H:i')."</td></tr>";
      //RazonSocial	puntoventa	numero	fecha	facturado	cobrado	descuento	IdCierreTurno
      //SANGRONIS ANA MARIA	13	5654	2020-06-29 15:33:14.553	1503.2824	1253.2800	250.0000	52209
      
    }
  }
  if($totalDescuentosNoRegistrados>0){
    $tablaDescuentos .= "<tr><td colspan=$colspan><b>DESCUENTO SERVICLUB APP YPF</b></td><td>$".round($totalDescuentosNoRegistrados,2)."</td></tr>";
  } else {
    $tablaDescuentos .= "<tr><td colspan=$colspan><b>NO HUBO OPERACIONES CON DESCUENTOS</b></td><td></td></tr>";
  }
  
  echo trim($tablaDescuentos);
}
function muestraDespachosGrandes($cortes=1){
  global $mssql, $articulo, $classArticulo, $tanque, $pico, $desde, $hasta;
  $tablaDescuentos="";
  $totalDescuentosNoRegistrados=0;
  $totalDespachosGrandes = 0;

  $sqlUltimasFacturasGrandes = "select IdArticulo, sum(Cantidad) as litros, IdTipoMovimiento, PuntoVenta, Numero, RazonSocial, CASE WHEN IdTipoMovimiento IN ('NCB', 'NCA', 'RDV') THEN -1 * Total ELSE Total END as Total, Fecha from dbo.movimientosFac a, dbo.MovimientosDetalleFac b where a.IdMovimientoFac=b.IdMovimientoFac AND idArticulo in (2069, 2068) AND Fecha >='".date('Y-m-d')."' AND Total>$cortes*9900 AND DocumentoAnticipado=0 AND b.ExcluidoDeTurno=0  AND (IdCondicionVenta>1 AND NumeroDocumento IS NOT NULL) AND IdMovimientoCancelado IS null AND DocumentoCancelado=0 group by idarticulo, IdTipoMovimiento, puntoventa, numero, RazonSocial, Total, Fecha order by a.fecha desc, a.razonsocial desc, idarticulo asc ;";
  
  $stmt = odbc_exec2($mssql, $sqlUltimasFacturasGrandes, __LINE__, __FILE__);
  
  while($fila = sqlsrv_fetch_array($stmt)){
    if($_SESSION['esMovil']){
      $numero = "$fila[PuntoVenta]";
      $limiteTextoMovil = 15;

    } else {
      $numero = "$fila[PuntoVenta]-$fila[Numero]";
      $limiteTextoMovil = 18;
    }
    $nombreCliente = ucwords(strtolower((strlen($fila['RazonSocial'])>$limiteTextoMovil)?substr($fila['RazonSocial'],0,$limiteTextoMovil).'...':$fila['RazonSocial']));
    $tablaDescuentos .= "<tr><td>$nombreCliente</td><td>$numero</td><td>".number_format($fila['litros'],2)." ".$articulo[$fila['IdArticulo']]['abr']."</td><td>$".round($fila['Total'],2)."</td><td>".$fila['Fecha']->format('H:i')."</td></tr>";
    $totalDespachosGrandes += $fila['litros'];
    //ChromePhp::log($articulo[$fila['IdArticulo']]);
    //print_r($articulo[$fila['IdArticulo']]);
  }
  if($tablaDescuentos==""){
    $tablaDescuentos = "<tr><td colspan='4'><b>NO HUBO DESPACHOS MAYORES A $cortes CORTES</b></td></tr>";
  }

  
  echo trim($tablaDescuentos);
  if($totalDespachosGrandes>0){
    echo "<tr><td colspan='3'><b>Total ".number_format($totalDespachosGrandes,2)." lts</b></td><td></td><td></td></tr>";
  }
}


switch($_POST['que']){
  case 'tanques':
    muestraDetallesTanquesTelemedidos();
    break;

  case 'despachos':
    muestraDetallesVentasDiarias();
    break;
    
  case 'descuentos':
    muestraDetalleDescuentosAPP();ChromePhp::log('Descuentos');
    break;

  case 'despachosGrandes':
    muestraDespachosGrandes();ChromePhp::log('despachosGrandes');
    break;

}
