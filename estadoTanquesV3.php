<?php
if(substr($_SERVER['REMOTE_ADDR'], 0, 10)=='192.168.1.'){
    $nivelRequerido=5;
}
include($_SERVER['DOCUMENT_ROOT'].'/include/inicia.php');

$titulo="Estado tanques | YPF";

// hacer ventas mensuales calculadas por diferencia aforadores ultimo cierre menos ultimo cierre mes anterior y sumar las ventas diarias.


if(!isset($_POST['desde'])){
  $desde = date('Y-m-d');
  $hasta = date("Y-m-d", time()+86400);
}


$sqlUltimoUpdate = $mysqli->query("SELECT fecha, tipo FROM ultimaactualizacion order by id desc limit 1");
ChromePhp::log($sqlUltimoUpdate);
$ultimoUpdate = $sqlUltimoUpdate->fetch_array();
$datetime1 = date_create($ultimoUpdate[0]);
$datetime2 = new DateTime("now");
$interval = date_diff($datetime1, $datetime2);
       
// desarrollo 3
  



function recepcionMensual(){
    global $mysqli;
    global $articulo, $mes, $combustible;
    //$sql="SELECT sum( ns ) , sum( np ) , sum( ud ) , sum( ed ) FROM `ventasDiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) AND MONTH( fecha ) = MONTH( CURDATE( ) ) ";
    
    $sql1 = "SELECT month(fecha) as mes,  sum( tq3 ) as l2078, sum( tq5 ) as l2076, sum( tq2 + tq6 ) as l2069, sum( tq1 + tq4 ) as l2068, count(remito2) as cuantoscamiones FROM `recepcioncombustibles` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR (YEAR(fecha)=YEAR(CURDATE())-1 AND MONTH(fecha)>=MONTH(CURDATE())-1) group by year(fecha),month(fecha)";
    

    //$sqlVentasMensuales = "SELECT month(fecha) as mes,  sum( ns ) as l2078, sum( np ) as l2076, sum( ud ) as l2069, sum( ed ) as l2068 FROM `ventasDiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR YEAR(fecha)=YEAR(CURDATE())-1 group by year(fecha),month(fecha)";
    
    $result = $mysqli->query($sql1);
    if($result&&$result->num_rows>0 &&!$_SESSION['esMovil']){
        $tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th></th><th>".ucwords(strtolower($articulo[2068]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2069]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2076]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2078]['descripcion']))."</th></tr></thead><tbody>";
        while($rowPromedioDiaSemana = $result->fetch_assoc()){
            //print_r($rowPromedioDiaSemana);
            //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
            //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
            $tablaPromedioDiaSemana.="<tr class='".
                    (((date("n")==$rowPromedioDiaSemana['mes'])||(date("N")==0))?'label-warning':'').
                    "'><td>".$mes[$rowPromedioDiaSemana['mes']]
                    ."</td><td>".$rowPromedioDiaSemana['cuantoscamiones']
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2068'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2069'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2076'])
                    ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2078'])
                    ."</td></tr>";
        }
        //$tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".sprintf("%01.2f",$promedioHistorico['l2068'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2069'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2076'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2078'])."</td></tr></tbody></table>";
        $tablaPromedioDiaSemana.="</tbody></table>";
    }
	echo trim($tablaPromedioDiaSemana);
}

if(isset($_GET['m'])){
  switch($_GET['m']){
    case 'detalleTanques':
      muestraDetalleTanques();
      break;
  }
  die;	
}



// Despachos extraidos de la tabla de despachos, contiene todo lo que salió de los surtidores y no solo lo facturado
if(date('H')>22&&false){
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-m-d 22:00:00")."' GROUP BY IdArticulo;";
  //$sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-d-m 22:00:00")."' GROUP BY IdArticulo;";
} else {
  $sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-m-d")."' GROUP BY IdArticulo;";
  //$sqlDespachos = "select IdArticulo, SUM(Cantidad), count(IdDespacho) from dbo.Despachos WHERE Fecha>='".date("Y-d-m")."' GROUP BY IdArticulo;";
}

$stmt = odbc_exec2( $mssql, $sqlDespachos, __LINE__, __FILE__);
while($row = sqlsrv_fetch_array($stmt)){
  $despachos[$row[0]]=$row[1];
  $despachos['d'.$row[0]]=$row[2];
}


// Promedio historico
// metodo nuevo (2014-11-17), lo calcula en base a la información de los cierres de turno

$sqlPromedioHistorico = "SELECT avg(ns) as l2078, avg(np) as l2076, avg(ud) as l2069, avg(ed) as l2068 FROM `ventasDiarias` WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}' AND month(fecha)=month(curdate()) ";
$sqlPromedioHistorico = "SELECT avg(ns) as l2078, avg(np) as l2076, avg(ud) as l2069, avg(ed) as l2068 FROM `ventasDiarias` WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}'";
$resultPromedioHistorico = $mysqli->query($sqlPromedioHistorico);
$promedioHistorico = $resultPromedioHistorico->fetch_assoc();

// Promedio diario
// metodo nuevo (2014-11-17), lo calcula en base a la información de los cierres de turno
$sqlPromedioDiario = "SELECT avg(ns) as l2078 , avg(np) as l2076 , avg(ud) as l2069, avg(ed) as l2068, diaSemana FROM `ventasDiarias`  WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}' AND month(fecha)=month(curdate()) GROUP BY diaSemana";
$sqlPromedioDiario = "SELECT avg(ns) as l2078 , avg(np) as l2076 , avg(ud) as l2069, avg(ed) as l2068, diaSemana FROM `ventasDiarias`  WHERE fecha>='{$CFG->fechaDesdeDondeTomoPromedioHistoricos}' GROUP BY diaSemana";
$result = $mysqli->query($sqlPromedioDiario);
if($result && $result->num_rows>0 && !$_SESSION['esMovil']){
  $tablaPromedioDiaSemana="<table class='table'><thead><tr><th></th><th>".ucwords(strtolower($articulo[2068]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2069]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2076]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2078]['descripcion']))."</th></tr></thead><tbody>";
  while($rowPromedioDiaSemana = $result->fetch_assoc()){
    //print_r($rowPromedioDiaSemana);
    //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
    //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
    $tablaPromedioDiaSemana.="<tr class='".
      (((date("N")==$rowPromedioDiaSemana['diaSemana'])||(date("N")==0))?'label-warning':'').
      "'><td>".$date2[$rowPromedioDiaSemana['diaSemana']]
      ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2068'])
      ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2069'])
      ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2076'])
      ."</td><td>".sprintf("%01.2f",$rowPromedioDiaSemana['l2078'])
      ."</td></tr>";
  }
  $tablaPromedioDiaSemana.="<tr class='label-info'><td>General</td><td>".sprintf("%01.2f",$promedioHistorico['l2068'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2069'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2076'])."</td><td>".sprintf("%01.2f",$promedioHistorico['l2078'])."</td></tr></tbody></table>";
}
   

// fecha y hora del ultimo cierre
$sqlUltimoCierre = "select top 1 dbo.CierresTurno.Fecha from dbo.CierresTurno order by IdCierreTurno DESC";
$stmt = odbc_exec2($mssql, $sqlUltimoCierre, __LINE__, __FILE__);
$rowUltimoCierre = sqlsrv_fetch_array($stmt);

$ultimoCierre=$rowUltimoCierre['Fecha']->format('Y-m-d H:i:s');

foreach($combustible as $key => $cb){
  //print_r($key);
  $stockCierre = sprintf("%01.2f", $cb['Medicion']);
  if(isset($despachos[$key])){
    $estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", $despachos[$key]);
    $estadoComb[$key]['stock']		= sprintf("%01.2f", $telemedidoXarticulo[$key]); //$despachos[$key]));
    $ocupado				= round((($estadoComb[$key]['stock'])/$cb['Capacidad'])*100,2);
    $porcentajeOcupado			= sprintf("%01.2f", $ocupado);
    $porcentajeOcupado			= round($ocupado,0);
    $estadoComb[$key]['stockInicial']	= sprintf("%01.2f", ($stockCierre-$cb['despachos']));
    $estadoComb[$key]['disponible']	= sprintf("%01.0f", round($cb['Capacidad']-$estadoComb[$key]['stock'],2));
    //$estadoComb[$key]['disponible'] 	= round($cb['Capacidad']-($stockCierre),2);
    $classNoVentas		= "";
    $despachoPromedio                     = sprintf("%01.1f", ($estadoComb[$key]['vtasDdeCierre']/$despachos['d'.$key]));
  } else {
    $estadoComb[$key]['vtasDdeCierre']	= sprintf("%01.2f", 0);
    $classNoVentas			= " class='noVentas'";
    $estadoComb[$key]['stockInicial']	= sprintf("%01.2f", $stockCierre);
    $estadoComb[$key]['stock']		= sprintf("%01.2f", $telemedidoXarticulo[$key]); //$despachos[$key]));
    $ocupado				= round(($estadoComb[$key]['stock']/$cb['Capacidad'])*100,2);
    $porcentajeOcupado			= round($ocupado,0);
    $estadoComb[$key]['disponible'] 	= sprintf("%01.0f", round($cb['Capacidad']-$estadoComb[$key]['stock'],2));
    $estadoComb[$key]['disponible'] 	= round($cb['Capacidad']-$stockCierre,2);
    $despachoPromedio                     = "0";
  }
  // modificacion para que el color del porcentaje varíe de acuerdo a cantidad de días de producto
  // echo $key;
  $limiteParaQuiebre	= ($key=='2069'||$key=='2068')?2400:1000;
  $classQuiebre		= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?' class="noVentas"':'';
  $promedio			= ($estadoComb[$key]['stock']<$limiteParaQuiebre)?'----':"(".round(($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key],1)." d.)";
  if(((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<1)||$estadoComb[$key]['stock']<$limiteParaQuiebre){$classAlerta='btn-danger btn btn-xs';}
  elseif((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<2){$classAlerta='btn-warning btn btn-xs';}
  elseif((($estadoComb[$key]['stock']-$limiteParaQuiebre)/$promedioHistorico['l'.$key])<3){$classAlerta='btn-info btn btn-xs';}
  else {$classAlerta='btn-success btn btn-xs';}
  /*
  if($ocupado<25)$classAlerta='btn-danger btn';
  elseif($ocupado<50)$classAlerta='btn-warning btn';
  elseif($ocupado<75)$classAlerta='btn-info btn';
  else $classAlerta='btn-success btn';
  */
  $d = (isset($despachos['d'.$key]))?$despachos['d'.$key]:0;
  $tabla2.="<tr$classQuiebre><th width='18%'>".$articulo[$cb['idArticulo']]['descripcion']."<br/>$cb[Capacidad] lts</th><td$classNoVentas>".$estadoComb[$key]['vtasDdeCierre']."<br>$d desp / $despachoPromedio lt</td><td>".$estadoComb[$key]['stock']."<br/>$promedio</td><td><span class='$classAlerta'>$porcentajeOcupado%</span></td><td>".$estadoComb[$key]['disponible']."</td></tr>";//<td>$stockCierre</td>
  // corregir que no duplique después de las 22 hs

  @$totalDiario['vtasDdeCierre']+=$estadoComb[$key]['vtasDdeCierre'];
  @$totalDiario['d']+=$d;
}
//print_r($estadoComb);
@$mixInfinia = 100*$estadoComb[2076]['vtasDdeCierre']/($estadoComb[2076]['vtasDdeCierre']+$estadoComb[2078]['vtasDdeCierre']);
@$mixEuro = 100*$estadoComb[2068]['vtasDdeCierre']/($estadoComb[2068]['vtasDdeCierre']+$estadoComb[2069]['vtasDdeCierre']);


// para corregir error después de las 0 de cada día mientras no haya despachos.
$totalDiario['d'] = ($totalDiario['d']<>0)?$totalDiario['d']:.0001;

$tabla2.="<tr class='well'><th width='18%'>Total</th><td>".$totalDiario['vtasDdeCierre']."<br>$totalDiario[d] desp / ".round($totalDiario['vtasDdeCierre']/$totalDiario['d'],2)." lts</td><td colspan=2>Estimado ".round($totalDiario['vtasDdeCierre']/(date("H")-6)*16)." lts<br/>Mix ".round($mixInfinia,1)."% | ".round($mixEuro,1)."%</td><td></td></tr>"; // basado en cálculos ultra precisos
$desdeHistorico = "2011-10-02";
$desdeHistorico = "2017-01-01";


// verifico que el historico no esté en sesion
if(!isset($_SESSION['despachosHorariosHistoricos'])||1){
  // saca promedio general desde el día 0 hasta hoy
  // select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$desdeHistorico',getdate()) from dbo.Despachos group by datepart(HOUR, Fecha) order by hora; 
  $sqlDespachosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$desdeHistorico',getdate()) as q from dbo.Despachos WHERE Fecha>='$desdeHistorico' group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosHistoricos, __FILE__, __LINE__);

  $despachosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $despachosHorariosHistoricos[$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosHistoricos']=$despachosHorariosHistoricos;
  $sqlLitrosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'$desdeHistorico',getdate()) as q from dbo.Despachos WHERE Fecha>='$desdeHistorico'  group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosHistoricos, __FILE__, __LINE__);

  $litrosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $litrosHorariosHistoricos[$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosHistoricos']=$litrosHorariosHistoricos;
}

if(!isset($_SESSION['despachosHorariosHistoricosDiarios'][date('w')])||1){
  // saca promedio general desde el día 0 hasta hoy
  // select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha))/DATEDIFF(day,'$desdeHistorico',getdate()) from dbo.Despachos group by datepart(HOUR, Fecha) order by hora;
  
  $sqlDespachosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, (count(datepart(HOUR, Fecha))/DATEDIFF(day,'$desdeHistorico',getdate()))*7 as q from dbo.Despachos WHERE DATEPART(dw,Fecha)=".(date('w')+1)." AND Fecha>='$desdeHistorico' group by datepart(HOUR, Fecha) order by hora;"; 
  $stmt = odbc_exec2($mssql, $sqlDespachosHorariosHistoricos, __FILE__, __LINE__);

  $despachosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $despachosHorariosHistoricos[date('w')][$row['hora']] = $row['q'];
  }
  $_SESSION['despachosHorariosHistoricosDiarios']=$despachosHorariosHistoricos;
  $sqlLitrosHorariosHistoricos = "select datepart(HOUR, Fecha) as hora, sum(Cantidad)/DATEDIFF(day,'$desdeHistorico',getdate())*7 as q from dbo.Despachos WHERE DATEPART(dw,Fecha)=".(date('w')+1)." AND Fecha>='$desdeHistorico' group by datepart(HOUR, Fecha) order by hora;";
  $stmt = odbc_exec2($mssql, $sqlLitrosHorariosHistoricos, __FILE__, __LINE__);

  $litrosHorariosHistoricos = array();
  while($row = sqlsrv_fetch_array($stmt)){
    if($row['hora']>5)
    $litrosHorariosHistoricos[date('w')][$row['hora']] = round($row['q'],1);
  }
  $_SESSION['litrosHorariosHistoricosDiarios']=$litrosHorariosHistoricos;
}


//ChromePhp::log($_SESSION['despachosHorariosHistoricos']);

// despachos por hora
$sqlDespachosHorariosActuales = "select datepart(HOUR, Fecha) as hora, count(datepart(HOUR, Fecha)) as q from dbo.Despachos where CONVERT(date, Fecha)=CONVERT(date, Getdate()) group by datepart(HOUR, Fecha) order by hora;";

$stmt = odbc_exec2($mssql, $sqlDespachosHorariosActuales, __FILE__, __LINE__);

while($row = sqlsrv_fetch_array($stmt)){
  if($row['hora']>5)
  $despachosHorariosActuales[$row['hora']]=$row['q'];
}
@ChromePhp::log($despachosHorariosActuales);
//// calcula estimacion hora actual
//$minuto = 
@$despachosHorariosActuales[date('G')] = round($despachosHorariosActuales[date('G')]/date('i')*60,0);
//ChromePhp::log($despachosHorariosActuales[date('G')]);
//ChromePhp::log($despachosHorariosActuales);
//ChromePhp::log(date('G'));
$max1 = max($_SESSION['despachosHorariosHistoricos']);
$max2 = max($despachosHorariosActuales);

$maximo = max($max1, $max2)+10;


// litros por hora
$sqlLitrosHorariosActuales = "select datepart(HOUR, Fecha) as hora, sum(Cantidad) as q from dbo.Despachos where CONVERT(date, Fecha)=CONVERT(date, Getdate()) group by datepart(HOUR, Fecha) order by hora;";

$stmt = odbc_exec2($mssql, $sqlLitrosHorariosActuales, __FILE__, __LINE__);

while($row = sqlsrv_fetch_array($stmt)){
  if($row['hora']>5)
  $litrosHorariosActuales[$row['hora']]=round($row['q'],1);
}
//ChromePhp::log($litrosHorariosActuales);
// calcula estimacion hora actual
@$litrosHorariosActuales[date('G')] = round($litrosHorariosActuales[date('G')]/date('i')*60,0);
//ChromePhp::log($despachosHorariosActuales[date('G')]);
//ChromePhp::log($litrosHorariosActuales);
//ChromePhp::log(date('G'));
$max1 = max($_SESSION['litrosHorariosHistoricos']);
$max2 = max($litrosHorariosActuales);

$maximo2 = max($max1, $max2)+100;



$sqlMangueras = "SELECT idManguera, IdArticulo FROM dbo.mangueras";
$stmt = odbc_exec2($mssql, $sqlMangueras, __FILE__, __LINE__);

$mangueras = array();
while($manguera = sqlsrv_fetch_array($stmt)){
    $mangueras[$manguera['idManguera']] = $manguera['IdArticulo'];
}


//$year = (date("n")==1)?date("Y")-1:date("Y");
$year = date("Y");


$ultimoDiaMesAnterior = date('Y-m-d', strtotime('last day of previous month'));
$ultimoCierre22 = (date('H')>22)?date('Y-m-d'):date('Y-m-d', strtotime('yesterday'));

$sqlAforadoresAlUltimoTurnoMesAnterior = "select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoDiaMesAnterior 19:00:00' and Fecha<'$ultimoDiaMesAnterior 23:59:59') UNION select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoCierre22 19:00:00' and Fecha<'$ultimoCierre22 23:59:59') order by IdCierreSurtidores desc";
#cuarentena
#$sqlAforadoresAlUltimoTurnoMesAnterior = "select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoDiaMesAnterior 12:00:00' and Fecha<'$ultimoDiaMesAnterior 23:59:59') UNION select IdManguera, AforadorElectronico, AforadorMecanico, d.IdCierreSurtidores, Fecha from dbo.CierresDetalleSurtidores as d, dbo.cierressurtidores as s where d.IdCierreSurtidores=s.IdCierreSurtidores AND d.IdCierreSurtidores=(select IdCierreSurtidores from dbo.CierresSurtidores where Fecha>='$ultimoCierre22 19:00:00' and Fecha<'$ultimoCierre22 23:59:59') order by IdCierreSurtidores desc";

//$sqlAforadoresAlUltimoTurnoMesAnterior = "select IdManguera, AforadorElectronico, AforadorMecanico, IdCierreSurtidores from dbo.CierresDetalleSurtidores where IdCierreSurtidores=(select top 1 IdCierreSurtidores from dbo.CierresSurtidores where Fecha<'".date("Y-m-01")."' order by Fecha desc) OR IdCierreSurtidores=(select top 1 IdCierreSurtidores from dbo.CierresSurtidores order by Fecha desc)  order by IdCierreDetalleSurtidores desc";
// echo $sqlAforadoresAlUltimoTurnoMesAnterior;
$stmt = odbc_exec2($mssql, $sqlAforadoresAlUltimoTurnoMesAnterior, __LINE__, __FILE__);
$signo = 1;
$sumaProductoElectronico = Array();
$sumaProductoMecanico = array();
while($aforadores = sqlsrv_fetch_array($stmt)){
    if(!isset($idCierreSurtidores)){
        $idCierreSurtidores = $aforadores['IdCierreSurtidores'];
    } elseif($idCierreSurtidores<>$aforadores['IdCierreSurtidores']){
        $idCierreSurtidores = $aforadores['IdCierreSurtidores'];
        $signo = -1;
    }
    if(isset($sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]])){
        $sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]] +=  $signo*$aforadores['AforadorElectronico'];
        $sumaProductoMecanico[$mangueras[$aforadores['IdManguera']]] +=  $signo*$aforadores['AforadorMecanico'];
    } else {
        $sumaProductoElectronico[$mangueras[$aforadores['IdManguera']]] =  $signo*$aforadores['AforadorElectronico'];
        $sumaProductoMecanico[$mangueras[$aforadores['IdManguera']]] =  $signo*$aforadores['AforadorMecanico'];
    }
    if(!isset($ultimoCierreAyer))$ultimoCierreAyer = $aforadores['Fecha'];
}
@$sqlVentasDesdeUltimoCierre = "SELECT IdArticulo, sum(cantidad) from dbo.despachos where fecha>='".$ultimoCierreAyer->format('Y-m-d H:i:s')."' group by IdArticulo; ";
//$sqlVentasDesdeUltimoCierre = "SELECT IdArticulo, sum(cantidad) from dbo.despachos where fecha>='".$ultimoCierreAyer."' group by IdArticulo; ";
//ChromePhp::log($sqlVentasDesdeAyer);
//echo $sqlVentasDesdeUltimoCierre;
$stmt = odbc_exec2($mssql, $sqlVentasDesdeUltimoCierre, __LINE__, __FILE__);

while($ventasDesdeUltimoCierre = sqlsrv_fetch_array($stmt)){
  $sumaProductoElectronico[$ventasDesdeUltimoCierre[0]] += $ventasDesdeUltimoCierre[1];
  $sumaProductoMecanico[$ventasDesdeUltimoCierre[0]] += $ventasDesdeUltimoCierre[1];
}


$sqlVentasMensuales = "SELECT month(fecha) as mes,  sum( ns ) as l2078, sum( np ) as l2076, sum( ud ) as l2069, sum( ed ) as l2068, year(fecha) as anio FROM `ventasDiarias` WHERE YEAR( fecha ) = YEAR( CURDATE( ) ) OR (YEAR(fecha)=YEAR(CURDATE())-1 AND MONTH(fecha)>=MONTH(CURDATE())-1) group by year(fecha),month(fecha)";
$result = $mysqli->query($sqlVentasMensuales);
if($result&&$result->num_rows>0 ){//&& !$_SESSION['esMovil']
    $tablaVentasMensuales="<table class='table'><thead><tr><th></th><th>".ucwords(strtolower($articulo[2068]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2069]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2076]['descripcion']))."</th><th>".ucwords(strtolower($articulo[2078]['descripcion']))."</th><th>Total</th></tr></thead><tbody>";
    while($rowPromedioDiaSemana = $result->fetch_assoc()){
      //print_r($rowPromedioDiaSemana);
      //Tengo que sumar en un array para cada tipo de combustible, contar y luego hacer promedio
      //Array ( [0] => Wednesday [] => 1084 [1] => 76340.9271 [2] => 2068 [IdArticulo] => 2068 [3] => 1084 )
      if(date("n")==$rowPromedioDiaSemana['mes']&&date("Y")==$rowPromedioDiaSemana['anio']){
          //nada 
      } else {
        $mixInfinia = round($rowPromedioDiaSemana['l2076']/($rowPromedioDiaSemana['l2076']+$rowPromedioDiaSemana['l2078']),2);
        $mixEuro = round($rowPromedioDiaSemana['l2068']/($rowPromedioDiaSemana['l2068']+$rowPromedioDiaSemana['l2069']),2);
        @$mixComb = 100*($rowPromedioDiaSemana['l2076']+$rowPromedioDiaSemana['l2078'])/($rowPromedioDiaSemana['l2076']+$rowPromedioDiaSemana['l2078']+$rowPromedioDiaSemana['l2068']+$rowPromedioDiaSemana['l2069']);
        $mix['success']=.25;
        $mix['info']=.20;
        $mix['warning']=.18;
        $colorMixInfinia = (($mixInfinia>$mix['success'])?'success':(($mixInfinia>$mix['info'])?'info':(($mixInfinia>$mix['warning'])?'warning':'danger')));
        $colorMixEuro = (($mixEuro>$mix['success'])?'success':(($mixEuro>$mix['info'])?'info':(($mixEuro>$mix['warning'])?'warning':'danger')));
        $tablaVentasMensuales.="<tr class='".
        (((date("n")==$rowPromedioDiaSemana['mes'])||(date("N")==0))?'label-warning':'').
        "'><td>".substr($mes[$rowPromedioDiaSemana['mes']],0,3)
        ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2068']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2068]['vtasDdeCierre']:0)). " <span class='label label-$colorMixEuro'>"
        .sprintf("%01.0f",100*$mixEuro).'%</span>'
        ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2069']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2069]['vtasDdeCierre']:0))
        ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2076']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2076]['vtasDdeCierre']:0)). " <span class='label label-$colorMixInfinia'>"
        .sprintf("%01.0f",100*$mixInfinia).'%</span>'
        ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2078']+((date("n")==$rowPromedioDiaSemana['mes'])?$estadoComb[2078]['vtasDdeCierre']:0))
        ."</td><td>".sprintf("%01.0f",$rowPromedioDiaSemana['l2078']+$rowPromedioDiaSemana['l2069']+$rowPromedioDiaSemana['l2068']+$rowPromedioDiaSemana['l2076']). "<sup>"
        .sprintf("%01.0f",$mixComb).'%</sup>'
        ."</td></tr>";
      }
    }
    $mixInfinia = round(($sumaProductoElectronico['2076']+$estadoComb['2076']['vtasDdeCierre'])/($sumaProductoElectronico['2076']+$estadoComb['2076']['vtasDdeCierre']+$sumaProductoElectronico['2078']+$estadoComb['2078']['vtasDdeCierre']),2);
    $mixEuro = round(($sumaProductoElectronico['2068']+$estadoComb['2068']['vtasDdeCierre'])/($sumaProductoElectronico['2068']+$estadoComb['2068']['vtasDdeCierre']+$sumaProductoElectronico['2069']+$estadoComb['2069']['vtasDdeCierre']),2);
    $colorMixInfinia = (($mixInfinia>.18)?'success':(($mixInfinia>.16)?'info':(($mixInfinia>.14)?'warning':'danger')));
    $colorMixEuro = (($mixEuro>.18)?'success':(($mixEuro>.16)?'info':(($mixEuro>.14)?'warning':'danger')));
    $totalMesActual = $sumaProductoElectronico['2078']+$sumaProductoElectronico['2076']+$sumaProductoElectronico['2068']+$sumaProductoElectronico['2069']+$estadoComb['2068']['vtasDdeCierre']+$estadoComb['2069']['vtasDdeCierre']+$estadoComb[2076]['vtasDdeCierre']+$estadoComb[2078]['vtasDdeCierre'];
    $totalMesEstimado = $totalMesActual/(date('d')-1+date('H')/24)*date('t');
    $tablaVentasMensuales.="<tr class='label-warning'><td>".$mes[date("n")]
    ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2068']+$estadoComb['2068']['vtasDdeCierre']). " <span class='label label-$colorMixEuro'>"
    .sprintf("%01.0f",100*$mixEuro).'%</span>'
    ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2069']+$estadoComb['2069']['vtasDdeCierre']) 
    ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2076']+$estadoComb['2076']['vtasDdeCierre']). " <span class='label label-$colorMixInfinia'>"
    .sprintf("%01.0f",100*$mixInfinia).'%</span>'
    ."</td><td>".sprintf("%01.0f",$sumaProductoElectronico['2078']+$estadoComb['2078']['vtasDdeCierre'])
    ."</td><td>".sprintf("%01.0f",$totalMesActual)
    ."<br/>".sprintf("%01.0f",$totalMesEstimado)."</td></tr>";
    $tablaVentasMensuales.="</tbody></table>";
}

if(!isset($_GET['soloComb'])){
  //print_r($estadoComb);
  function d($fecha, $incluyeHora=false){
    if($incluyeHora){
      $part = explode(' ', $fecha);
      $dia = explode('-', $part[0]);
      return $dia[2].'/'.$dia[1].' '.$part[1]; 
    } else {
      $part = explode('-', $fecha);	
      return $part[2].'/'.$part[1];
    }
  }

  function muestraOrdenes(){
    // desestimado por cambio en web ypf.com 
    echo"<table class='table'><tbody></tbody></table>";

  }
}


?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <?php include($_SERVER['DOCUMENT_ROOT'].'/include/head.php');?>
    <link rel="stylesheet" href="css/jquery.modal.css" type="text/css" media="screen" />
    <style type="text/css">
        body{
          <?php if(!$_SESSION['esMovil']){?>
          margin: 50px auto;
          <?php } else {?>
          margin: 10px 0;
          <?php }?>
        }

    </style>
    <?php if(isset($_GET['soloComb'])){?><link href="css/graficobarras.css" rel="stylesheet" type="text/css" media="screen"/><?php }; ?>
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print"/>
  </head>
  <body>
	<?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){include($_SERVER['DOCUMENT_ROOT']."/include/menuSuperior.php");} ?>
	<?php //if(!isset($_GET['soloComb'])){include("include/menuSuperior.php");} ?>
    <div class="container">
		<!-- Example row of columns -->
		<div class="row">
            <div class='col-md-5'>
                <div class="panel panel-primary" id='box1'>
                  <div class="panel-heading">
                      <h3 class="panel-title" id='fechaHora'>Combustibles <span id='ultimoDespacho'><?php echo date("d/m/y H:i:s")?></span><span id='refresh' class='pull-right glyphicon glyphicon-refresh gly-spin'></span></h3>
                  </div>
                  <div class="panel-body">
                      <table class='table' id='table1'>
                          <thead><tr><th></th><th>Ventas</th><th>Actual</th><th>Lleno</th><th>Vacío</th></tr></thead>
                          <tbody>
                            <tr><td colspan='8' rowspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>
                          </tbody>
                      </table>
                  </div>
                </div>
            </div>
            <div class='col-md-5'>
            <div class="panel panel-primary" id='box2'>
               <div class="panel-heading">
                    <h3 class="panel-title">Detalle tanques</h3>
               </div>
               <div class="panel-body gris" id="panelDetalle">
                  <table class='table' id='table2'>
                      <thead><tr><th></th><th>Actual</th> <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                      &nbsp;</th><th>Vacío</th></tr></thead>
                      <tbody>
                        <tr><td colspan='8' rowspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>
                      </tbody>
                  </table>
                </div>
            </div>
          </div>

        </div>
        <div class='row'>
          <div id="chartContainer" style="height: 200px; " class='col-md-10'></div>
        </div>
        <div class='row'>
          <div id="chartContainer2" style="height: 200px; " class='col-md-10'></div>
        </div>
        
        
        
        
        <div class='row'>
          <?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){?>
            <div class='col-md-5'>
              <div class="panel panel-primary" id='ventasDiarias'>
                <div class="panel-heading">
                    <h3 class="panel-title">Promedio diario 2016-<?php echo date("Y")?></h3>
                </div>
                <div class="panel-body gris" id="panelDetalle">
                    <?php echo $tablaPromedioDiaSemana; ?>
                </div>
              </div>
            </div>
          <?php } ?>
            
            <div class='col-md-5'>
              <div class="panel panel-primary" id='ventasDiarias'>
                <div class="panel-heading">
                    <h3 class="panel-title">Descuentos APP YPF</h3>
                </div>
                <div class="panel-body gris" >
                  <table class='table' id='descuentosAPP'>
                    <thead><tr><th>Cliente</th><th>Ticket</th><th>Total</th><th>-10%/15%</th><th>Serviclub</th><th>Hora</th></tr></thead>
                    <tbody>
                      <tr><td colspan='8' rowspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>
                    </tbody>
                  </table>
                  </div>
              </div>
            </div>
            <div class='col-md-5'>
              <div class="panel panel-primary" id='ventasDiarias'>
                <div class="panel-heading">
                    <h3 class="panel-title">Despachos grandes</h3>
                </div>
                <div class="panel-body gris" >
                  <table class='table' id='despachosGrandes'>
                    <thead><tr><th>Cliente</th><th>Ticket</th><th>Cantidad</th><th>Total</th><th>Hora</th></tr></thead>
                    <tbody>
                      <tr><td colspan='8' rowspan='8'><center><img src='img/ajax-loader.gif'/></center></td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
	</div>
        <div class='row'>
            <div class='col-md-5'>
			<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Despachado mensual</h3>
                </div>
                <div class="panel-body gris">
                    <?php echo $tablaVentasMensuales;?>
                </div>
            </div>            </div>

            <div></div><?php if(!isset($_GET['soloComb'])&&!$_SESSION['esMovil']){?>
            <div class='col-md-5'>
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Recibido mensual</h3>
                </div>
                <div class="panel-body gris">
                    <?php recepcionMensual();?>
                </div>
            </div>
            </div><?php } ?>
        </div>
        
        <?php /*if(!$_SESSION['esMovil']){echo "<h1> no es movil</h1>";} else {echo "<h1> SI es movil</h1>"; }*/?>
        <?php if(!$_SESSION['esMovil']){?>
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        </div><!-- /.modal-dialog -->
        <?php } 
        include($_SERVER['DOCUMENT_ROOT'].'/include/footer.php')?>
    </div> <!-- /container -->
	<?php include($_SERVER['DOCUMENT_ROOT'].'/include/termina.php');?>
	<script type="text/javascript" src="js/jquery.modal.min.js" charset="utf-8"></script>
	<script type="text/javascript" src="js/canvasjs.min.js"></script>
	<script type="text/javascript">
          window.onload = function () {
            var chart = new CanvasJS.Chart("chartContainer",
            {
              zoomEnabled: false,
              animationEnabled: true,
              axisY2:{
                valueFormatString:"0",
                maximum: <?php echo $maximo?>,
                interval: 10,
                interlacedColor: "#F5F5F5",
                gridColor: "#D7D7D7",      
                tickColor: "#D7D7D7"
              },
              theme: "theme2",
              toolTip:{
                shared: true
              },
              legend:{
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 15,
                fontFamily: "Lucida Sans Unicode"

              },
              data: [
              {        
                type: "line",
                lineThickness:3,
                axisYType:"secondary",
                showInLegend: true,           
                name: "Histórico", 
                dataPoints: [
                <?php foreach($_SESSION['despachosHorariosHistoricos'] as $hora => $despachos){
                  if($hora>5){
                  if(isset($coma1))echo","; else $coma1=1;
                  echo "{ x: $hora, y: $despachos }";
                  }
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "Actual",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($despachosHorariosActuales as $hora => $despachos){
                  if($hora>5){
                  if(isset($coma2))echo","; else $coma2=1;
                  echo "{ x: $hora, y: $despachos }";
                  }
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "<?php echo date('l');?>",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($_SESSION['despachosHorariosHistoricosDiarios'][date('w')] as $hora => $despachos){
                  if($hora>5){
                  if(isset($coma5))echo","; else $coma5=1;
                  echo "{ x: $hora, y: $despachos }";
                  }
                }?>
                ]
              }
              ],
            legend: {
              cursor:"pointer",
              itemclick : function(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
                }
                else {
                  e.dataSeries.visible = true;
                }
                chart.render();
              }
            }
          });
          chart.render();
          
          var chart = new CanvasJS.Chart("chartContainer2",
            {
              zoomEnabled: false,
              animationEnabled: true,
              axisY2:{
                valueFormatString:"0",
                maximum: <?php echo $maximo2?>,
                interval: <?php echo $maximo2/6?>,
                interlacedColor: "#F5F5F5",
                gridColor: "#D7D7D7",      
                tickColor: "#D7D7D7"
              },
              theme: "theme2",
              toolTip:{
                shared: true
              },
              legend:{
                verticalAlign: "bottom",
                horizontalAlign: "center",
                fontSize: 15,
                fontFamily: "Lucida Sans Unicode"

              },
              data: [
              {        
                type: "line",
                lineThickness:3,
                axisYType:"secondary",
                showInLegend: true,           
                name: "Histórico", 
                dataPoints: [
                <?php foreach($_SESSION['litrosHorariosHistoricos'] as $hora => $despachos){
                  if(isset($coma3))echo","; else $coma3=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "Actual",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($litrosHorariosActuales as $hora => $despachos){
                  if(isset($coma4))echo","; else $coma4=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              },
              {        
                type: "line",
                lineThickness:3,
                showInLegend: true,           
                name: "<?php echo date('l')?>",
                axisYType:"secondary",
                dataPoints: [
                <?php foreach($_SESSION['litrosHorariosHistoricosDiarios'][date('w')] as $hora => $despachos){
                  if(isset($coma6))echo","; else $coma6=1;
                  echo "{ x: $hora, y: $despachos }";
                }?>
                ]
              }
              ],
            legend: {
              cursor:"pointer",
              itemclick : function(e) {
                if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
                }
                else {
                  e.dataSeries.visible = true;
                }
                chart.render();
              }
            }
          });
          chart.render();
        }

        $(document).ready(function() {
          var ultimoDespacho = 0;
          function update(que, IdArticulo){
            $('#refresh').addClass('gly-spin');
            if(que=='tanques'){
              $.post('func/ajaxEstado.php', { desde: $('#rangoFin').val(), hasta: $('#rangoInicio').val(), que: 'tanques'}, function(data) {
                $('#table2 tbody').html(data);
            $('#refresh').removeClass('gly-spin');
              });
            } else if (que=='despachos'){
              $(".ud").removeClass('flash');
              $.post('func/ajaxEstado.php', { desde: $('#rangoFin').val(), hasta: $('#rangoInicio').val(), que: 'despachos'}, function(data) {
                $('#table1 tbody').html(data);
                if(IdArticulo!==''){
                  $("#ud_"+IdArticulo).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
                  $("#ud_"+IdArticulo).addClass('flash');
                }
                $('#refresh').removeClass('gly-spin');
              });
            } else if (que=='descuentos'){
              $.post('func/ajaxEstado.php', { desde: $('#rangoFin').val(), hasta: $('#rangoInicio').val(), que: 'descuentos'}, function(data) {
                $('#descuentosAPP tbody').html(data);

                $('#refresh').removeClass('gly-spin');
              });
            } else if (que=='despachosGrandes'){
              $.post('func/ajaxEstado.php', { desde: $('#rangoFin').val(), hasta: $('#rangoInicio').val(), que: 'despachosGrandes'}, function(data) {
                $('#despachosGrandes tbody').html(data);

                $('#refresh').removeClass('gly-spin');
              });
            }
          }
          setInterval(function(){
            $.post('func/ajaxUltimoDespacho.php', function(data) {
              if(ultimoDespacho !== data.ultimoDespacho){
                update('tanques', '');
                update('despachos', data.IdArticulo);
                update('descuentos', '');
                update('despachosGrandes', '');
                $('#ultimoDespacho').html(data.ultimoDespacho);
                ultimoDespacho = data.ultimoDespacho;
              }
            }, "json");
          }, 3000);
          
          $('#refresh').click(function(){
            $(this).addClass('gly-spin');
            $.post('func/ajaxUltimoDespacho.php', function(data) {
              update('tanques', '');
              update('despachos', data.IdArticulo);
              update('descuentos', '');
              update('despachosGrandes', '');
              $('#ultimoDespacho').html(data.ultimoDespacho);
              ultimoDespacho = data.ultimoDespacho;
            }, "json");
          });
        });
	</script>
  </body>
</html>
